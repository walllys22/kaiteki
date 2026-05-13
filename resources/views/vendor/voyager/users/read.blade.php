@extends('voyager::master')

@section('page_title', 'Ver Usuario')

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered" style="margin-bottom:0;">
                    <div class="panel-body" style="padding: 10px 15px; display:flex; align-items:center; justify-content:space-between;">
                        <h1 class="page-title" style="margin:0; font-size:20px;">
                            <i class="voyager-person"></i> Viendo Usuario
                        </h1>
                        <div>
                            @can('edit', $user)
                            <a href="{{ route('voyager.users.edit', $user->id) }}" class="btn btn-info btn-sm">
                                <i class="voyager-edit"></i> Editar
                            </a>
                            @endcan
                            <a href="{{ route('voyager.users.index') }}" class="btn btn-warning btn-sm">
                                <i class="voyager-list"></i> Volver a la lista
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="page-content container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-bordered">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="voyager-person"></i> Información del Usuario
                        </h3>
                    </div>
                    <div class="panel-body">
                        <table class="table table-condensed" style="margin-bottom:0;">
                            <tbody>
                                <tr>
                                    <th style="width:200px; color:#777;">Persona vinculada</th>
                                    <td>
                                        @if($user->person)
                                            {{ $user->person->first_name }}
                                        @else
                                            <span class="text-muted">Sin persona vinculada</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="color:#777;">Correo electrónico</th>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <th style="color:#777;">Sucursal / Dojo</th>
                                    <td>
                                        @if($user->dojo)
                                            {{ $user->dojo->nombre }}
                                        @else
                                            <span class="label label-default">Global (sin sucursal)</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="color:#777;">Rol</th>
                                    <td>
                                        @if($user->role)
                                            <span class="label label-primary">{{ $user->role->display_name }}</span>
                                        @else
                                            <span class="text-muted">Sin rol</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="color:#777;">Estado</th>
                                    <td>
                                        @if($user->status)
                                            <span class="label label-success">Habilitado</span>
                                        @else
                                            <span class="label label-danger">Inhabilitado</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="color:#777;">Registrado</th>
                                    <td>{{ \Carbon\Carbon::parse($user->created_at)->format('d/m/Y H:i') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
