@extends('layouts.app')
@section('title', 'Buat Slip Gaji')
@section('page-title', 'Buat Slip Gaji')
@section('content')
    @include('payroll-slips._form', ['isEdit' => false, 'payrollSlip' => null])
@endsection
