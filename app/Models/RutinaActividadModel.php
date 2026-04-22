<?php

namespace App\Models;

use CodeIgniter\Model;

class RutinaActividadModel extends Model
{
    protected $table            = 'rutinas_actividades';
    protected $primaryKey       = 'id_actividad';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['nombre', 'descripcion', 'frecuencia', 'peso', 'activa'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
