<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Document extends Model
{
    // Spécifiez la collection si besoin, par défaut 'documents'
    protected $collection = 'documents';
    protected $primaryKey = 'doc_id'; // Spécifiez la clé primaire si différente

    protected $fillable = [
        'doc_id',
        'doc_name',
        'doc_type',
        'doc_content',
        'doc_format',
        'doc_insert_date',
        'doc_updated_date',
        'doc_file_full_path',
    ];

    // Si vous souhaitez gérer les dates automatiquement, vous pouvez ajouter :
    protected $dates = ['doc_insert_date', 'doc_updated_date'];
}
