<?php

namespace App\Models;

use CodeIgniter\Model;

class RutinaRegistroModel extends Model
{
    protected $table            = 'rutinas_registros';
    protected $primaryKey       = 'id_registro';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['id_usuario', 'id_actividad', 'fecha', 'completada', 'hora_completado'];

    protected $useTimestamps = false;
}
