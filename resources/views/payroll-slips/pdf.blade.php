<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Slip Gaji - {{ $payrollSlip->slip_number }}</title>
@include('payroll-slips._pdf-styles')
</head>
<body>

@include('payroll-slips._pdf-body', ['payrollSlip' => $payrollSlip])

</body>
</html>
