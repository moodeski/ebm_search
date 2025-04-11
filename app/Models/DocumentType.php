<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class DocumentType extends Model
{
    // Spécifiez la collection dédiée aux types de documents
    protected $collection = 'document_types';

    // Champs pouvant être assignés en masse
    protected $fillable = ['name'];
}
