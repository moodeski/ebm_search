<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentTypeController;


Route::get('/', [DocumentController::class, 'index'])->name('documents.index');
Route::get('/document/create', [DocumentController::class, 'create'])->name('documents.create');
Route::post('/document/store', [DocumentController::class, 'store'])->name('documents.store');
Route::get('/document/{id}/edit', [DocumentController::class, 'edit'])->name('documents.edit');
Route::put('/document/{id}', [DocumentController::class, 'update'])->name('documents.update');
Route::delete('/document/{id}', [DocumentController::class, 'destroy'])->name('documents.destroy');
Route::get('/search', [DocumentController::class, 'search'])->name('documents.search');
Route::get('/document/{id}/download', [DocumentController::class, 'download'])->name('documents.download');


// Routes pour la gestion des types de documents
Route::get('/document-types', [DocumentTypeController::class, 'index'])->name('document_types.index');
Route::get('/document-types/create', [DocumentTypeController::class, 'create'])->name('document_types.create');
Route::post('/document-types', [DocumentTypeController::class, 'store'])->name('document_types.store');
Route::get('/document-types/{id}/edit', [DocumentTypeController::class, 'edit'])->name('document_types.edit');
Route::put('/document-types/{id}', [DocumentTypeController::class, 'update'])->name('document_types.update');
Route::delete('/document-types/{id}', [DocumentTypeController::class, 'destroy'])->name('document_types.destroy');



