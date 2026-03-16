@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border">
            <div class="card-body text-center py-5">
                <i class="bi bi-speedometer2" style="font-size: 4rem; color: var(--blue-1);"></i>
                <h3 class="mt-3">Bienvenue sur KEUF Engineering</h3>
                <p class="text-secondary">Sélectionnez un module dans le menu pour commencer</p>
                
                <div class="row mt-4 justify-content-center">
                    <div class="col-md-3">
                        <div class="card border">
                            <div class="card-body">
                                <i class="bi bi-building fs-1" style="color: var(--blue-1);"></i>
                                <h6 class="mt-2">Projets</h6>
                                <a href="#" class="btn btn-grey-keuf btn-sm mt-2">Accéder</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card border">
                            <div class="card-body">
                                <i class="bi bi-people fs-1" style="color: var(--green);"></i>
                                <h6 class="mt-2">Employés</h6>
                                <a href="#" class="btn btn-grey-keuf btn-sm mt-2">Accéder</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card border">
                            <div class="card-body">
                                <i class="bi bi-box fs-1" style="color: var(--orange);"></i>
                                <h6 class="mt-2">Stocks</h6>
                                <a href="#" class="btn btn-grey-keuf btn-sm mt-2">Accéder</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection