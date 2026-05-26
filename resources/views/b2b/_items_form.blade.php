{{--
    Shared line items table for Quotation / Invoice forms.
    Required vars:
        $items     – Collection|array of existing items (each with description, quantity, unit, unit_price, amount)
        $document  – the Quotation|Invoice model (or null for create)
--}}
@php $items = $items ?? collect(); @endphp

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong><i class="bi bi-list-ol me-1 text-primary"></i> Item / Layanan</strong>
        <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn">
            <i class="bi bi-plus-circle"></i> Tambah Baris
        </button>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle" id="itemsTable">
            <thead style="font-size:.78rem;background:#f8fafc">
                <tr>
                    <th style="width:36px"></th>
                    <th>Deskripsi <span class="text-danger">*</span></th>
                    <th style="width:90px">Qty</th>
                    <th style="width:90px">Unit</th>
                    <th style="width:140px">Harga Satuan</th>
                    <th style="width:140px" class="text-end">Subtotal</th>
                    <th style="width:40px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $i => $it)
                <tr class="item-row">
                    <td class="text-muted small text-center row-num">{{ $i + 1 }}</td>
                    <td><input type="text" name="items[{{ $i }}][description]" class="form-control form-control-sm" required value="{{ old("items.$i.description", $it->description ?? $it['description'] ?? '') }}"></td>
                    <td><input type="number" step="0.01" min="0.01" name="items[{{ $i }}][quantity]" class="form-control form-control-sm item-qty" required value="{{ old("items.$i.quantity", $it->quantity ?? $it['quantity'] ?? 1) }}"></td>
                    <td><input type="text" name="items[{{ $i }}][unit]" class="form-control form-control-sm" maxlength="30" value="{{ old("items.$i.unit", $it->unit ?? $it['unit'] ?? '') }}" placeholder="pcs/jam"></td>
                    <td><input type="number" step="0.01" min="0" name="items[{{ $i }}][unit_price]" class="form-control form-control-sm item-price" required value="{{ old("items.$i.unit_price", $it->unit_price ?? $it['unit_price'] ?? 0) }}"></td>
                    <td class="text-end font-monospace item-amount" style="font-size:.85rem">0</td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger btn-icon remove-row"><i class="bi bi-x"></i></button></td>
                </tr>
                @empty
                <tr class="item-row">
                    <td class="text-muted small text-center row-num">1</td>
                    <td><input type="text" name="items[0][description]" class="form-control form-control-sm" required></td>
                    <td><input type="number" step="0.01" min="0.01" name="items[0][quantity]" class="form-control form-control-sm item-qty" required value="1"></td>
                    <td><input type="text" name="items[0][unit]" class="form-control form-control-sm" maxlength="30" placeholder="pcs/jam"></td>
                    <td><input type="number" step="0.01" min="0" name="items[0][unit_price]" class="form-control form-control-sm item-price" required value="0"></td>
                    <td class="text-end font-monospace item-amount" style="font-size:.85rem">0</td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger btn-icon remove-row"><i class="bi bi-x"></i></button></td>
                </tr>
                @endforelse
            </tbody>
            <tfoot style="font-size:.85rem">
                <tr>
                    <td colspan="5" class="text-end text-muted">Subtotal</td>
                    <td class="text-end font-monospace fw-semibold" id="sumSubtotal">0</td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="5" class="text-end text-muted">
                        Diskon (Rp)
                    </td>
                    <td><input type="number" step="0.01" min="0" name="discount" id="inputDiscount" class="form-control form-control-sm text-end" value="{{ old('discount', $document->discount ?? 0) }}"></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="4" class="text-end text-muted">PPN (%)</td>
                    <td><input type="number" step="0.01" min="0" max="100" name="tax_percent" id="inputTax" class="form-control form-control-sm" value="{{ old('tax_percent', $document->tax_percent ?? 11) }}"></td>
                    <td class="text-end font-monospace" id="sumTax">0</td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="5" class="text-end fw-bold">Total</td>
                    <td class="text-end font-monospace fw-bold text-success" id="sumTotal" style="font-size:1rem">0</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script>
(function(){
    var tbody = document.querySelector('#itemsTable tbody');
    var addBtn = document.getElementById('addItemBtn');

    function fmt(n){ return 'Rp ' + Number(n||0).toLocaleString('id-ID', {minimumFractionDigits:0, maximumFractionDigits:2}); }
    function reindex() {
        Array.from(tbody.querySelectorAll('.item-row')).forEach(function(row, idx){
            row.querySelector('.row-num').textContent = idx + 1;
            row.querySelectorAll('input').forEach(function(inp){
                var name = inp.getAttribute('name');
                if(!name) return;
                inp.setAttribute('name', name.replace(/items\[\d+\]/, 'items['+idx+']'));
            });
        });
    }
    function recalcRow(row) {
        var q = parseFloat(row.querySelector('.item-qty').value) || 0;
        var p = parseFloat(row.querySelector('.item-price').value) || 0;
        var amt = q * p;
        row.querySelector('.item-amount').textContent = fmt(amt);
        return amt;
    }
    function recalcAll() {
        var subtotal = 0;
        Array.from(tbody.querySelectorAll('.item-row')).forEach(function(r){ subtotal += recalcRow(r); });
        var discount = parseFloat(document.getElementById('inputDiscount').value) || 0;
        var taxPct   = parseFloat(document.getElementById('inputTax').value) || 0;
        var after    = Math.max(0, subtotal - discount);
        var taxAmt   = after * (taxPct/100);
        document.getElementById('sumSubtotal').textContent = fmt(subtotal);
        document.getElementById('sumTax').textContent      = fmt(taxAmt);
        document.getElementById('sumTotal').textContent    = fmt(after + taxAmt);
    }
    function addRow() {
        var idx = tbody.querySelectorAll('.item-row').length;
        var tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML =
            '<td class="text-muted small text-center row-num">'+(idx+1)+'</td>'+
            '<td><input type="text" name="items['+idx+'][description]" class="form-control form-control-sm" required></td>'+
            '<td><input type="number" step="0.01" min="0.01" name="items['+idx+'][quantity]" class="form-control form-control-sm item-qty" required value="1"></td>'+
            '<td><input type="text" name="items['+idx+'][unit]" class="form-control form-control-sm" maxlength="30" placeholder="pcs/jam"></td>'+
            '<td><input type="number" step="0.01" min="0" name="items['+idx+'][unit_price]" class="form-control form-control-sm item-price" required value="0"></td>'+
            '<td class="text-end font-monospace item-amount" style="font-size:.85rem">0</td>'+
            '<td><button type="button" class="btn btn-sm btn-outline-danger btn-icon remove-row"><i class="bi bi-x"></i></button></td>';
        tbody.appendChild(tr);
        recalcAll();
    }
    addBtn.addEventListener('click', addRow);
    document.addEventListener('input', function(e){
        if(e.target.closest('#itemsTable')) recalcAll();
    });
    document.addEventListener('click', function(e){
        var btn = e.target.closest('.remove-row');
        if(!btn) return;
        if(tbody.querySelectorAll('.item-row').length <= 1){ alert('Minimal 1 baris item.'); return; }
        btn.closest('.item-row').remove();
        reindex();
        recalcAll();
    });
    recalcAll();
})();
</script>
