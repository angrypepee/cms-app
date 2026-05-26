<div class="slip-doc">

    {{-- ─── HEADER ─── --}}
    <div class="slip-header">
        <table>
            <tr>
                <td>
                    <table>
                        <tr>
                            <td style="width: 64px;">
                                @if($payrollSlip->company->logo && file_exists(storage_path('app/public/' . $payrollSlip->company->logo)))
                                    <img src="{{ storage_path('app/public/' . $payrollSlip->company->logo) }}" class="company-logo" alt="">
                                @else
                                    <div class="logo-placeholder">{{ strtoupper(substr($payrollSlip->company->name, 0, 1)) }}</div>
                                @endif
                            </td>
                            <td class="company-block">
                                <div class="slip-company-name">{{ $payrollSlip->company->name }}</div>
                                @php
                                    $meta = array_filter([
                                        $payrollSlip->company->address ?? null,
                                        $payrollSlip->company->phone ?? null,
                                        $payrollSlip->company->email ?? null,
                                    ]);
                                @endphp
                                @if(!empty($meta))
                                    <div class="slip-company-meta">{{ implode(' · ', $meta) }}</div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="right">
                    <div class="slip-label">Slip Gaji Karyawan</div>
                    <div class="slip-title">{{ $payrollSlip->period_label }}</div>
                    <div class="slip-num">{{ $payrollSlip->slip_number }}</div>
                    <span class="badge-status {{ $payrollSlip->status }}">{{ strtoupper($payrollSlip->status) }}</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- ─── EMPLOYEE INFO ─── --}}
    <div class="slip-emp">
        <div class="slip-section-label">Informasi Karyawan</div>
        @php
            $fields = [
                ['Nama Lengkap',       $payrollSlip->employee->name, false, true],
                ['ID Karyawan',        $payrollSlip->employee->employee_id, true],
                ['Jabatan',            $payrollSlip->employee->position ?? '-'],
                ['Departemen',         $payrollSlip->employee->department ?? '-'],
                ['Golongan',           $payrollSlip->employee->grade ?? '-'],
                ['Kategori',           $payrollSlip->employee->employee_category?->label() ?? '-'],
                ['Tanggal Pembayaran', $payrollSlip->payment_date ? $payrollSlip->payment_date->format('d M Y') : '-'],
            ];
            if ($payrollSlip->cutoff_start && $payrollSlip->cutoff_end) {
                $fields[] = ['Periode Cutoff', $payrollSlip->cutoff_start->format('d M Y') . ' – ' . $payrollSlip->cutoff_end->format('d M Y')];
            }
            if ($payrollSlip->employee->bank_name || $payrollSlip->employee->bank_account) {
                $fields[] = ['Rekening Bank', trim(($payrollSlip->employee->bank_name ?? '') . ' ' . ($payrollSlip->employee->bank_account ?? ''))];
            }
            if ($payrollSlip->employee->npwp) {
                $fields[] = ['NPWP', $payrollSlip->employee->npwp, true];
            }
            $rows = array_chunk($fields, 3);
        @endphp
        <table class="emp-grid">
            @foreach($rows as $row)
            <tr>
                @foreach($row as $f)
                <td>
                    <div class="emp-field-label">{{ $f[0] }}</div>
                    <div class="{{ !empty($f[3]) ? 'emp-name' : 'emp-field-value' }} {{ !empty($f[2]) ? 'mono' : '' }}">{{ $f[1] }}</div>
                </td>
                @endforeach
                @for($i = count($row); $i < 3; $i++)<td></td>@endfor
            </tr>
            @endforeach
        </table>
    </div>

    {{-- ─── INCOME & DEDUCTIONS ─── --}}
    <div class="slip-items-section">
        <table class="items-wrap">
            <tr>
                <td>
                    <div class="items-col-head income">+ Pendapatan</div>
                    <table class="items-tbl">
                        <tbody>
                            @foreach($payrollSlip->incomes as $item)
                            <tr>
                                <td>{{ $item->label }}</td>
                                <td class="amt">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="in-foot">
                            <tr>
                                <td>Total Pendapatan</td>
                                <td class="amt">Rp {{ number_format($payrollSlip->total_income, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </td>
                <td>
                    <div class="items-col-head deduction">− Potongan</div>
                    <table class="items-tbl">
                        <tbody>
                            @forelse($payrollSlip->deductions as $item)
                            <tr>
                                <td>{{ $item->label }}</td>
                                <td class="amt">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" style="color:#94a3b8;text-align:center;padding:10px;font-size:9.5px">Tidak ada potongan</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="de-foot">
                            <tr>
                                <td>Total Potongan</td>
                                <td class="amt">Rp {{ number_format($payrollSlip->total_deduction, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    {{-- ─── CALC SUMMARY ─── --}}
    <div class="slip-calc">
        <table>
            <tr>
                <td></td>
                <td>
                    <div class="calc-label">Total Pendapatan</div>
                    <div class="calc-val income">Rp {{ number_format($payrollSlip->total_income, 0, ',', '.') }}</div>
                </td>
                <td style="width:18px"><span class="calc-op">−</span></td>
                <td>
                    <div class="calc-label">Total Potongan</div>
                    <div class="calc-val deduction">Rp {{ number_format($payrollSlip->total_deduction, 0, ',', '.') }}</div>
                </td>
                <td style="width:18px"><span class="calc-op">=</span></td>
                <td>
                    <div class="calc-label">Take Home Pay</div>
                    <div class="calc-val thp">Rp {{ number_format($payrollSlip->take_home_pay, 0, ',', '.') }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ─── THP BANNER ─── --}}
    <div class="slip-thp">
        <table>
            <tr>
                <td>
                    <div class="thp-eyebrow">Take Home Pay</div>
                    <div class="thp-period-sub">{{ $payrollSlip->period_label }}</div>
                </td>
                <td class="right">
                    <div class="thp-amount">Rp {{ number_format($payrollSlip->take_home_pay, 0, ',', '.') }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ─── NOTES ─── --}}
    @if($payrollSlip->notes)
    <div class="slip-notes">
        <div class="notes-title">Catatan</div>
        <div class="notes-text">{{ $payrollSlip->notes }}</div>
    </div>
    @endif

    {{-- ─── SIGNATURES ─── --}}
    <div class="slip-signatures">
        <table class="sig-table">
            <tr>
                <td>
                    <div class="sig-title">Diterima oleh,</div>
                    @if($payrollSlip->isEmployeeSigned())
                        <div class="sig-space signed-emp">
                            <span class="sig-stamp emp">Digital<br>Signed</span>
                        </div>
                        <div class="sig-name">{{ $payrollSlip->employee->name }}</div>
                        <div class="sig-role">Karyawan</div>
                        <div class="sig-meta">✓ {{ $payrollSlip->employee_signed_at->format('d M Y, H:i') }} WIB</div>
                    @else
                        <div class="sig-space"></div>
                        <div class="sig-name">{{ $payrollSlip->employee->name }}</div>
                        <div class="sig-role">Karyawan</div>
                        <div class="sig-meta pending">Belum ditandatangani</div>
                    @endif
                </td>
                <td>
                    <div class="sig-title">Disetujui &amp; Ditandatangani oleh,</div>
                    @if($payrollSlip->isSigned() && $payrollSlip->signer)
                        <div class="sig-space signed-mgr">
                            <span class="sig-stamp mgr">Digital<br>Signature</span>
                        </div>
                        <div class="sig-name">{{ $payrollSlip->signer->name }}</div>
                        <div class="sig-role">{{ $payrollSlip->signer->title ?? $payrollSlip->signer->role?->label() }}</div>
                        <div class="sig-meta">✓ {{ $payrollSlip->signed_at->format('d M Y, H:i') }} WIB</div>
                    @else
                        <div class="sig-space"></div>
                        <div class="sig-name">HRD / Management</div>
                        <div class="sig-role">{{ $payrollSlip->company->name }}</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- ─── FOOTER ─── --}}
    <div class="slip-footer">
        <table>
            <tr>
                <td>
                    <div class="slip-footer-text">
                        Dokumen ini diterbitkan secara resmi oleh <strong>{{ $payrollSlip->company->name }}</strong>.<br>
                        Slip gaji ini sah sebagai bukti pembayaran dan tidak memerlukan tanda tangan basah.
                    </div>
                </td>
                <td class="right" style="width:38%">
                    <div class="slip-footer-id">
                        Diterbitkan&nbsp;·&nbsp;{{ $payrollSlip->slip_number }}&nbsp;·&nbsp;{{ ($payrollSlip->released_at ?? $payrollSlip->created_at)->format('d M Y') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

</div>
