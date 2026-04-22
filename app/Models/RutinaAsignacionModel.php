<?php

namespace App\Models;

use CodeIgniter\Model;

class RutinaAsignacionModel extends Model
{
    protected $table            = 'rutinas_asignaciones';
    protected $primaryKey       = 'id_asignacion';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['id_usuario', 'id_actividad', 'activa'];

    protected $useTimestamps = false;
}
