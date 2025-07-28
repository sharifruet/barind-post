<?php
namespace App\Models;
use CodeIgniter\Model;

class TagModel extends Model
{
    protected $table = 'tags';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name'];
    protected $returnType = 'array';
    public $timestamps = false;
} 