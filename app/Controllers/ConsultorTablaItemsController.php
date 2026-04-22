<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\DashboardItemModel;

class ConsultorTablaItemsController extends Controller
{
    public function index()
    {
        $model = new DashboardItemModel();
        $items = $model->where('activo', 1)
            ->orderBy('categoria', 'ASC')
            ->orderBy('orden', 'ASC')
            ->findAll();

        // TAT Fase 1: tarjetas ocultas por estar fuera de alcance.
        // Para restaurar una tarjeta, elimina su "detalle" de este array.
        $itemsOcultos = [
            'Otto - Asistente IA',
            'Monitor Otto',
        ];
        $items = array_filter($items, static fn($it) => !in_array($it['detalle'] ?? '', $itemsOcultos, true));

        // Agrupar por categoría
        $grouped = [];
        foreach ($items as $item) {
            $cat = $item['categoria'] ?? 'Sin categoría';
            $grouped[$cat][] = $item;
        }

        // Orden de las categorías en el dashboard
        $ordenCategorias = [
            'IA y Asistencia',
            'Operación Diaria',
            'Gestión Clientes',
            'Inspecciones y Auditoría',
            'Cumplimiento y Control',
            'Planeación SST',
            'Dashboards Analíticos',
            'Gestión Documental',
            'Carga Masiva CSV',
            'Usuarios y Accesos',
            'Configuración',
            'Administración',
        ];

        $sortedGroups = [];
        foreach ($ordenCategorias as $cat) {
            if (isset($grouped[$cat])) {
                $sortedGroups[$cat] = $grouped[$cat];
            }
        }
        // Agregar categorías no listadas al final
        foreach ($grouped as $cat => $items) {
            if (!isset($sortedGroups[$cat])) {
                $sortedGroups[$cat] = $items;
            }
        }

        $data['grouped'] = $sortedGroups;

        return view('consultant/dashboard', $data);
    }
}
