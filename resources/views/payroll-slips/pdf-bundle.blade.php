<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>{{ $title ?? 'Slip Gaji - Bundle' }}</title>
@include('payroll-slips._pdf-styles')
<style>
    .slip-page-break { page-break-after: always; }
    .slip-page-break:last-child { page-break-after: auto; }
</style>
</head>
<body>

@foreach($slips as $payrollSlip)
    <div class="slip-page-break">
        @include('payroll-slips._pdf-body', ['payrollSlip' => $payrollSlip])
    </div>
@endforeach

</body>
</html>
