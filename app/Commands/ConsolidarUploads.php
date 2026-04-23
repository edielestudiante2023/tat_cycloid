<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class ConsolidarUploads extends BaseCommand
{
    protected $group       = 'Mantenimiento';
    protected $name        = 'uploads:consolidar';
    protected $description = 'Mueve todos los soportes a public/uploads/ con la nueva estructura y reescribe rutas en BD.';
    protected $usage       = 'uploads:consolidar [--dry] [--skip-db] [--skip-files]';

    private bool $dry = false;
    private array $logLines = [];

    public function run(array $params)
    {
        $this->dry = CLI::getOption('dry') !== null;
        $skipDb    = CLI::getOption('skip-db') !== null;
        $skipFiles = CLI::getOption('skip-files') !== null;

        CLI::write('=== uploads:consolidar ' . ($this->dry ? '(DRY-RUN)' : '(EJECUCIÓN REAL)') . ' ===', $this->dry ? 'yellow' : 'green');
        CLI::newLine();

        $this->crearEstructura();

        if (! $skipFiles) {
            $this->moverWritableSoportesClientes();
            $this->moverNitFoldersPublic();
            $this->renombrarPlanillas();
            $this->moverUploadsRaizLegacy();
        } else {
            CLI::write('— Saltando movimientos físicos (--skip-files)', 'light_gray');
        }

        if (! $skipDb) {
            CLI::newLine();
            $this->reescribirBD();
        } else {
            CLI::write('— Saltando reescritura en BD (--skip-db)', 'light_gray');
        }

        $this->guardarLog();

        CLI::newLine();
        CLI::write('=== Fin ===', 'green');
    }

    // ─── 1. Crear estructura objetivo ────────────────────────────────────
    private function crearEstructura(): void
    {
        CLI::write('── Crear estructura en public/uploads/ ──', 'light_cyan');

        $dirs = [
            UPLOADS_CLIENTES,
            UPLOADS_CLIENTES_DOCS,
            UPLOADS_CONSULTORES . 'firmas/',
            UPLOADS_CONSULTORES . 'fotos/',
            UPLOADS_CONTRATOS,
            UPLOADS_INFORMES,
            UPLOADS_TMP,
            UPLOADS_INSPECCIONES,
            UPLOADS_FIRMAS,
            UPLOADS_BRANDING,
            FCPATH . 'uploads/planillas-ss/',
            FCPATH . 'uploads/matrices/',
            FCPATH . 'uploads/informe-avances/',
            FCPATH . 'uploads/imagenesplanemergencias/',
        ];

        foreach ($dirs as $d) {
            if (is_dir($d)) {
                $this->log("DIR OK    $d");
                continue;
            }
            if ($this->dry) {
                $this->log("DIR MKDIR $d");
                CLI::write("  [mkdir] $d", 'light_gray');
            } else {
                @mkdir($d, 0755, true);
                $this->log("DIR MKDIR $d");
                CLI::write("  mkdir $d", 'green');
            }
        }

        // .htaccess deny en tmp/
        $htTmp = UPLOADS_TMP . '.htaccess';
        if (! is_file($htTmp)) {
            if ($this->dry) {
                $this->log("HTACCESS  $htTmp");
            } else {
                @file_put_contents($htTmp, "Require all denied\n");
                $this->log("HTACCESS  $htTmp");
            }
        }
    }

    // ─── 2. Mover writable/soportes-clientes/* → public/uploads/ ────────
    private function moverWritableSoportesClientes(): void
    {
        CLI::newLine();
        CLI::write('── Mover writable/soportes-clientes/ ──', 'light_cyan');

        $src = WRITEPATH . 'soportes-clientes';
        if (! is_dir($src)) {
            CLI::write('  (no existe, saltando)', 'light_gray');
            return;
        }

        $mapa = [
            'firmas_consultores' => UPLOADS_CONSULTORES . 'firmas/',
            'logos-clientes'     => UPLOADS_CLIENTES_DOCS,
            'pdfsalvados'        => UPLOADS_INFORMES,
        ];

        foreach (new \DirectoryIterator($src) as $entry) {
            if ($entry->isDot()) continue;

            $name = $entry->getFilename();

            if (isset($mapa[$name])) {
                $this->moverContenido($entry->getPathname(), $mapa[$name]);
            } elseif ($entry->isDir() && preg_match('/^\d+$/', $name)) {
                // Carpeta por NIT → clientes/{nit}/
                $this->moverContenido($entry->getPathname(), UPLOADS_CLIENTES . $name . '/');
            } else {
                CLI::write("  [?] sin destino definido: $name", 'yellow');
                $this->log("SKIP      $name (sin destino)");
            }
        }
    }

    // ─── 3. Mover public/uploads/{NIT}/ → public/uploads/clientes/{NIT}/
    private function moverNitFoldersPublic(): void
    {
        CLI::newLine();
        CLI::write('── Mover public/uploads/{NIT}/ → clientes/{NIT}/ ──', 'light_cyan');

        $src = FCPATH . 'uploads';
        foreach (new \DirectoryIterator($src) as $entry) {
            if (! $entry->isDir() || $entry->isDot()) continue;
            $name = $entry->getFilename();
            if (! preg_match('/^\d+$/', $name)) continue;

            $this->moverContenido($entry->getPathname(), UPLOADS_CLIENTES . $name . '/');
            // Eliminar la carpeta origen si quedó vacía
            if (! $this->dry && is_dir($entry->getPathname()) && count(scandir($entry->getPathname())) <= 2) {
                @rmdir($entry->getPathname());
                $this->log("RMDIR     " . $entry->getPathname());
            }
        }
    }

    // ─── 4. Renombrar planillas-seguridad-social → planillas-ss ────────
    private function renombrarPlanillas(): void
    {
        CLI::newLine();
        CLI::write('── Renombrar planillas-seguridad-social → planillas-ss ──', 'light_cyan');

        $src = FCPATH . 'uploads/planillas-seguridad-social';
        if (! is_dir($src)) {
            CLI::write('  (no existe, saltando)', 'light_gray');
            return;
        }
        $this->moverContenido($src, FCPATH . 'uploads/planillas-ss/');
        if (! $this->dry && is_dir($src) && count(scandir($src)) <= 2) {
            @rmdir($src);
            $this->log("RMDIR     $src");
        }
    }

    // ─── 5. Legacy uploads/inspecciones (raíz) → public/uploads/inspecciones
    private function moverUploadsRaizLegacy(): void
    {
        CLI::newLine();
        CLI::write('── Mover uploads/ raíz legacy ──', 'light_cyan');

        $src = ROOTPATH . 'uploads/inspecciones';
        if (! is_dir($src)) {
            CLI::write('  (no existe, saltando)', 'light_gray');
            return;
        }
        $this->moverContenido($src, UPLOADS_INSPECCIONES);
    }

    // ─── Helper: mover todo el contenido de $srcDir a $destDir ─────────
    private function moverContenido(string $srcDir, string $destDir): void
    {
        if (! is_dir($srcDir)) return;
        if (! is_dir($destDir)) {
            if ($this->dry) {
                $this->log("DIR MKDIR $destDir");
            } else {
                @mkdir($destDir, 0755, true);
            }
        }

        $iter = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($srcDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iter as $item) {
            $rel = substr($item->getPathname(), strlen($srcDir) + 1);
            $target = rtrim($destDir, '/\\') . DIRECTORY_SEPARATOR . $rel;

            if ($item->isDir()) {
                if (! is_dir($target)) {
                    if ($this->dry) {
                        $this->log("DIR MKDIR $target");
                    } else {
                        @mkdir($target, 0755, true);
                    }
                }
                continue;
            }

            if (is_file($target)) {
                $this->log("SKIP(dup) $target  (ya existe)");
                CLI::write("  [dup] $rel ya existe en destino, saltando", 'yellow');
                continue;
            }

            if ($this->dry) {
                $this->log("MOVE      $srcDir/$rel → $target");
            } else {
                if (! @rename($item->getPathname(), $target)) {
                    // rename puede fallar cross-device
                    if (@copy($item->getPathname(), $target)) {
                        @unlink($item->getPathname());
                    } else {
                        CLI::write("  ERROR moviendo: $rel", 'red');
                        $this->log("ERROR     $rel");
                        continue;
                    }
                }
                $this->log("MOVE      $rel → $target");
            }
        }

        CLI::write("  " . ($this->dry ? '[dry]' : 'OK') . "  $srcDir → $destDir", 'green');
    }

    // ─── 6. Reescribir rutas en BD ──────────────────────────────────────
    private function reescribirBD(): void
    {
        CLI::write('── Reescribir rutas en BD ──', 'light_cyan');

        $db = Database::connect();

        // Reglas de reemplazo: columna → [patrón => reemplazo]
        $reglas = [
            // Firmas consultores
            'tbl_consultor'  => [
                'firma_consultor' => ['firmas_consultores/' => 'consultores/firmas/'],
                'foto_consultor'  => ['firmas_consultores/' => 'consultores/firmas/'],
            ],
            // Reportes
            'tbl_reporte' => [
                'enlace' => [
                    '/serve-file/firmas_consultores/' => '/uploads/consultores/firmas/',
                    '/serve-file/' => '/uploads/',
                ],
            ],
            // Contratos
            'tbl_contratos' => [
                'ruta_pdf_contrato' => [
                    'serve-file/' => 'uploads/',
                ],
            ],
            // Planillas seguridad social
            'tbl_planilla_seg_social' => [
                'archivo' => ['planillas-seguridad-social/' => 'planillas-ss/'],
            ],
        ];

        foreach ($reglas as $tabla => $cols) {
            if (! $db->tableExists($tabla)) {
                CLI::write("  (tabla $tabla no existe, saltando)", 'light_gray');
                continue;
            }
            foreach ($cols as $col => $reemplazos) {
                $fields = $db->getFieldData($tabla);
                $exists = false;
                foreach ($fields as $f) {
                    if ($f->name === $col) { $exists = true; break; }
                }
                if (! $exists) {
                    CLI::write("  (columna $tabla.$col no existe, saltando)", 'light_gray');
                    continue;
                }

                foreach ($reemplazos as $buscar => $cambiar) {
                    $sql = "SELECT COUNT(*) AS n FROM `$tabla` WHERE `$col` LIKE ?";
                    $row = $db->query($sql, ["%$buscar%"])->getRow();
                    $n = (int) ($row->n ?? 0);
                    if ($n === 0) {
                        CLI::write("  [0] $tabla.$col ~ '$buscar'", 'light_gray');
                        continue;
                    }
                    if ($this->dry) {
                        CLI::write("  [dry] UPDATE $tabla SET $col = REPLACE(..., '$buscar', '$cambiar') — $n filas", 'yellow');
                        $this->log("SQL DRY   UPDATE $tabla SET $col=REPLACE (=$n)");
                    } else {
                        $db->query(
                            "UPDATE `$tabla` SET `$col` = REPLACE(`$col`, ?, ?) WHERE `$col` LIKE ?",
                            [$buscar, $cambiar, "%$buscar%"]
                        );
                        CLI::write("  UPDATE $tabla.$col '$buscar' → '$cambiar': $n filas", 'green');
                        $this->log("SQL RUN   $tabla.$col $buscar→$cambiar ($n)");
                    }
                }
            }
        }
    }

    // ─── Log ────────────────────────────────────────────────────────────
    private function log(string $line): void
    {
        $this->logLines[] = $line;
    }

    private function guardarLog(): void
    {
        if (empty($this->logLines)) return;
        $dir = WRITEPATH . 'logs';
        if (! is_dir($dir)) @mkdir($dir, 0755, true);
        $file = $dir . '/consolidar-uploads-' . date('Y-m-d_His') . ($this->dry ? '_dry' : '') . '.log';
        @file_put_contents($file, implode("\n", $this->logLines) . "\n");
        CLI::newLine();
        CLI::write("Log: $file", 'light_cyan');
    }
}
