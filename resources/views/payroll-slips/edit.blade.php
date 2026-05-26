@extends('layouts.app')
@section('title', 'Edit Slip Gaji')
@section('page-title', 'Edit Slip Gaji — ' . $payrollSlip->slip_number)
@section('content')
    @include('payroll-slips._form', ['isEdit' => true, 'payrollSlip' => $payrollSlip])
@endsection
