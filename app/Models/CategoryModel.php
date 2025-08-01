<?php
namespace App\Models;
use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'slug'];
    protected $returnType = 'array';
    public $timestamps = false;
} 