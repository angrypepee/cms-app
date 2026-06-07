<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeDocumentController;
use App\Http\Controllers\EmployeePortalController;
use App\Http\Controllers\PayrollSlipController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BonusController;
use App\Http\Controllers\AppreciationBudgetController;
use App\Http\Controllers\AppreciationClaimController;
use App\Http\Controllers\ReimbursementController;

use App\Http\Controllers\HolidayController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\InternalRequestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\OvertimeRequestController;
use App\Http\Controllers\PayrollInfoController;
use App\Http\Controllers\ContractDocumentController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\CmsController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectPlanController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\InvoiceController;

Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout',[AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ── Public share links for B2B documents (no auth) ────────
Route::prefix('p')->group(function () {
    Route::get('quotation/{token}',     [\App\Http\Controllers\PublicDocumentController::class, 'quotation'])->name('public.quotation');
    Route::get('quotation/{token}/pdf', [\App\Http\Controllers\PublicDocumentController::class, 'quotationPdf'])->name('public.quotation.pdf');
    Route::get('invoice/{token}',       [\App\Http\Controllers\PublicDocumentController::class, 'invoice'])->name('public.invoice');
    Route::get('invoice/{token}/pdf',   [\App\Http\Controllers\PublicDocumentController::class, 'invoicePdf'])->name('public.invoice.pdf');
});

// ── Protected routes ──────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ── Notifications (all authenticated users) ────────────
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/',              [NotificationController::class, 'index'])->name('index');
        Route::patch('read-all',     [NotificationController::class, 'markAllRead'])->name('read-all');
        Route::delete('delete-all',  [NotificationController::class, 'destroyAll'])->name('delete-all');
        Route::get('{id}/read',      [NotificationController::class, 'markAsRead'])->name('read');
        Route::delete('{id}',        [NotificationController::class, 'destroy'])->name('destroy');
    });

    // ── Employee portal (self-service) ─────────────────────
    Route::get('/my',                [EmployeePortalController::class, 'dashboard'])->name('my.dashboard');
    Route::get('/my/profile',        [EmployeePortalController::class, 'myProfile'])->name('my.profile');
    Route::patch('/my/profile',      [EmployeePortalController::class, 'updateProfile'])->name('my.profile.update');
    Route::get('/my/slips',          [EmployeePortalController::class, 'slips'])->name('my.slips');
    Route::get('/my/slips/{payrollSlip}', [EmployeePortalController::class, 'showSlip'])->name('my.slips.show');
    Route::post('/my/slips/{payrollSlip}/sign', [EmployeePortalController::class, 'signSlip'])->name('my.slips.sign');
    Route::get('/my/appreciation',   [EmployeePortalController::class, 'appreciation'])->name('my.appreciation');
    Route::post('/my/appreciation/{budget}/claims',
        [EmployeePortalController::class, 'storeClaim'])->name('my.appreciation.claims.store');
    Route::get('/my/appreciation/{budget}/claims/{claim}',
        [EmployeePortalController::class, 'showClaim'])->name('my.appreciation.claims.show');
    Route::get('/my/appreciation/{budget}/claims/{claim}/transfer-proof',
        [EmployeePortalController::class, 'showClaimTransferProof'])->name('my.appreciation.claims.transfer-proof');
    Route::get('/my/appreciation/{budget}/claims/{claim}/documents/{document}',
        [EmployeePortalController::class, 'showClaimDocument'])->name('my.appreciation.claims.documents.show');
    Route::delete('/my/appreciation/{budget}/claims/{claim}',
        [EmployeePortalController::class, 'destroyClaim'])->name('my.appreciation.claims.destroy');

    // ── Reimbursement portal (employee) ────────────────────
    Route::get('/my/reimbursements',
        [EmployeePortalController::class, 'reimbursements'])->name('my.reimbursements');
    Route::post('/my/reimbursements',
        [EmployeePortalController::class, 'storeReimbursement'])->name('my.reimbursements.store');
    Route::get('/my/reimbursements/{reimbursement}',
        [EmployeePortalController::class, 'showReimbursement'])->name('my.reimbursements.show');
    Route::delete('/my/reimbursements/{reimbursement}',
        [EmployeePortalController::class, 'destroyReimbursement'])->name('my.reimbursements.destroy');
    Route::get('/my/reimbursements/{reimbursement}/transfer-proof',
        [EmployeePortalController::class, 'showReimbursementTransferProof'])->name('my.reimbursements.transfer-proof');
    Route::get('/my/reimbursements/{reimbursement}/documents/{document}',
        [EmployeePortalController::class, 'showReimbursementDocument'])->name('my.reimbursements.documents.show');

    // ── Employee portal — Calendar, Leaves, Announcements, Requests ───────
    Route::get('/my/calendar',            [EmployeePortalController::class, 'calendar'])->name('my.calendar');
    Route::get('/my/leaves',              [EmployeePortalController::class, 'leaves'])->name('my.leaves');
    Route::post('/my/leaves',             [EmployeePortalController::class, 'storeLeave'])->name('my.leaves.store');
    Route::get('/my/leaves/{leaveRequest}', [EmployeePortalController::class, 'showLeave'])->name('my.leaves.show');
    Route::delete('/my/leaves/{leaveRequest}', [EmployeePortalController::class, 'destroyLeave'])->name('my.leaves.destroy');
    Route::get('/my/announcements',       [EmployeePortalController::class, 'announcements'])->name('my.announcements');
    Route::get('/my/requests',            [EmployeePortalController::class, 'myRequests'])->name('my.requests');
    Route::post('/my/requests',           [EmployeePortalController::class, 'storeRequest'])->name('my.requests.store');
    Route::get('/my/requests/{internalRequest}', [EmployeePortalController::class, 'showRequest'])->name('my.requests.show');

    // ── Attendance (employee self-service) ─────────────────
    Route::post('/my/attendance/check-in',  [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('/my/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');

    // ── Overtime requests (employee portal) ────────────────
    Route::get('/my/overtime',      [OvertimeRequestController::class, 'myIndex'])->name('my.overtime');
    Route::post('/my/overtime',     [OvertimeRequestController::class, 'myStore'])->name('my.overtime.store');
    Route::get('/my/projects',      [EmployeePortalController::class, 'myProjects'])->name('my.projects');
    Route::patch('/my/projects/{project}/work-status', [EmployeePortalController::class, 'updateWorkStatus'])->name('my.projects.work-status');
    Route::get('/my/contracts',      [EmployeePortalController::class, 'myContracts'])->name('my.contracts');
    Route::patch('/my/contracts/{contract}/sign',   [EmployeePortalController::class, 'signContractAsEmployee'])->name('my.contracts.sign');
    Route::patch('/my/contracts/{contract}/reject', [EmployeePortalController::class, 'rejectContract'])->name('my.contracts.reject');

    // ── Staff-only routes (admin, HR, signature_admin) ─────
    Route::middleware('not-employee')->group(function () {

        Route::resource('companies', CompanyController::class);

        // ── Attendance admin view ──────────────────────────────
        Route::get('attendance/today', [AttendanceController::class, 'todayJson'])->name('attendance.today-json');

        // ── Overtime requests (admin/HR) ───────────────────────
        Route::get('overtime',                            [OvertimeRequestController::class, 'index'])->name('overtime.index');
        Route::get('overtime/{overtimeRequest}',          [OvertimeRequestController::class, 'show'])->name('overtime.show');
        Route::patch('overtime/{overtimeRequest}/approve',[OvertimeRequestController::class, 'approve'])->name('overtime.approve');
        Route::patch('overtime/{overtimeRequest}/reject', [OvertimeRequestController::class, 'reject'])->name('overtime.reject');

        // ── Payroll information (admin/HR) ──────────────────────
        Route::get('kontrak-kerja', [PayrollInfoController::class, 'contractIndex'])->name('kontrak-kerja.index');
        Route::get('kontrak-kerja/{employee}', [PayrollInfoController::class, 'contractShow'])->name('kontrak-kerja.show');
        Route::get('contract-documents/supporting-data/{employee}', [ContractDocumentController::class, 'supportingData'])->name('contract-documents.supporting-data');
        Route::post('contract-documents/save-template', [ContractDocumentController::class, 'saveTemplateFromCreate'])->name('contract-documents.save-template');
        Route::resource('contract-documents', ContractDocumentController::class);
        Route::patch('contract-documents/{contractDocument}/sign', [ContractDocumentController::class, 'sign'])->name('contract-documents.sign');
        Route::patch('contract-documents/{contractDocument}/unsign', [ContractDocumentController::class, 'unsign'])->name('contract-documents.unsign');
        Route::patch('contract-documents/{contractDocument}/cancel', [ContractDocumentController::class, 'cancel'])->name('contract-documents.cancel');
        Route::get('contract-documents/{contractDocument}/download', [ContractDocumentController::class, 'download'])->name('contract-documents.download');
        Route::get('payroll-info', [PayrollInfoController::class, 'index'])->name('payroll-info.index');
        Route::get('payroll-info/report', [PayrollInfoController::class, 'report'])->name('payroll-info.report');
        Route::post('payroll-info/transfer/{employee}', [PayrollInfoController::class, 'transfer'])->name('payroll-info.transfer');
        Route::post('payroll-info/transfer-all',         [PayrollInfoController::class, 'transferAll'])->name('payroll-info.transfer-all');
        Route::post('payroll-info/update-sign-date',     [PayrollInfoController::class, 'updateSignDate'])->name('payroll-info.update-sign-date');

        Route::resource('employees', EmployeeController::class);
        Route::get('companies/{company}/employees', [EmployeeController::class, 'getByCompany'])
            ->name('companies.employees');

        // ── Employee documents ─────────────────────────────────
        Route::post('employees/{employee}/documents',               [EmployeeDocumentController::class, 'store'])->name('employee-documents.store');
        Route::get('employees/{employee}/documents/{document}',    [EmployeeDocumentController::class, 'show'])->name('employee-documents.show');
        Route::delete('employees/{employee}/documents/{document}', [EmployeeDocumentController::class, 'destroy'])->name('employee-documents.destroy');

        Route::post('employees/{employee}/portfolios',                                        [\App\Http\Controllers\EmployeePortfolioController::class, 'store'])->name('employee-portfolios.store');
        Route::get('employees/{employee}/portfolios/{portfolio}',                              [\App\Http\Controllers\EmployeePortfolioController::class, 'show'])->name('employee-portfolios.show');
        Route::delete('employees/{employee}/portfolios/{portfolio}',                           [\App\Http\Controllers\EmployeePortfolioController::class, 'destroy'])->name('employee-portfolios.destroy');

        Route::get('payroll-slips/bulk-create',   [PayrollSlipController::class, 'bulkCreate'])->name('payroll-slips.bulk-create');
        Route::post('payroll-slips/bulk-store',   [PayrollSlipController::class, 'bulkStore'])->name('payroll-slips.bulk-store');
        Route::post('payroll-slips/bulk-download',[PayrollSlipController::class, 'bulkDownload'])->name('payroll-slips.bulk-download');
        Route::resource('payroll-slips', PayrollSlipController::class);
        Route::get('payroll-slips/{payrollSlip}/pdf',     [PayrollSlipController::class, 'downloadPdf'])->name('payroll-slips.pdf');
        Route::patch('payroll-slips/{payrollSlip}/publish',[PayrollSlipController::class, 'publish'])->name('payroll-slips.publish');
        Route::patch('payroll-slips/{payrollSlip}/sign',   [PayrollSlipController::class, 'sign'])->name('payroll-slips.sign');

        // ── User management (admin only) ───────────────────────
        Route::resource('users', UserController::class)->only(['index', 'edit', 'update']);
        Route::patch('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

        // ── Employee login account management ──────────────────
        Route::post('employees/{employee}/create-account',   [UserController::class, 'createEmployeeAccount'])->name('employees.create-account');
        Route::delete('employees/{employee}/revoke-account', [UserController::class, 'revokeEmployeeAccount'])->name('employees.revoke-account');

        // ── Bonus Karyawan ─────────────────────────────────────
        Route::resource('bonuses', BonusController::class);
        Route::patch('bonuses/{bonus}/pay', [BonusController::class, 'markPaid'])->name('bonuses.pay');

        // ── Uang Apresiasi ─────────────────────────────────────
        Route::resource('appreciation', AppreciationBudgetController::class)
            ->except(['edit', 'update']);

        // Claims (nested under appreciation budget)
        Route::get('appreciation/{appreciation}/claims/create',
            [AppreciationClaimController::class, 'create'])->name('appreciation.claims.create');
        Route::post('appreciation/{appreciation}/claims',
            [AppreciationClaimController::class, 'store'])->name('appreciation.claims.store');
        Route::get('appreciation/{appreciation}/claims/{claim}',
            [AppreciationClaimController::class, 'show'])->name('appreciation.claims.show');
        Route::patch('appreciation/{appreciation}/claims/{claim}/approve',
            [AppreciationClaimController::class, 'approve'])->name('appreciation.claims.approve');
        Route::patch('appreciation/{appreciation}/claims/{claim}/reject',
            [AppreciationClaimController::class, 'reject'])->name('appreciation.claims.reject');
        Route::delete('appreciation/{appreciation}/claims/{claim}',
            [AppreciationClaimController::class, 'destroy'])->name('appreciation.claims.destroy');
        Route::get('appreciation/{appreciation}/claims/{claim}/transfer-proof',
            [AppreciationClaimController::class, 'showTransferProof'])->name('appreciation.claims.transfer-proof');

        // Claim documents
        Route::post('appreciation/{appreciation}/claims/{claim}/documents',
            [AppreciationClaimController::class, 'addDocument'])->name('appreciation.claims.documents.store');
        Route::get('appreciation/{appreciation}/claims/{claim}/documents/{document}',
            [AppreciationClaimController::class, 'showDocument'])->name('appreciation.claims.documents.show');
        Route::delete('appreciation/{appreciation}/claims/{claim}/documents/{document}',
            [AppreciationClaimController::class, 'deleteDocument'])->name('appreciation.claims.documents.destroy');

        // ── Employee toggle active ─────────────────────────────
        Route::patch('employees/{employee}/toggle-active', [EmployeeController::class, 'toggleActive'])->name('employees.toggle-active');

        // ── User toggle active ─────────────────────────────────
        Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');

        // ── Calendar / Holidays ────────────────────────────────
        Route::get('calendar',                     [HolidayController::class, 'index'])->name('calendar.index');
        Route::post('calendar/holidays',           [HolidayController::class, 'store'])->name('calendar.holidays.store');
        Route::put('calendar/holidays/{holiday}',  [HolidayController::class, 'update'])->name('calendar.holidays.update');
        Route::delete('calendar/holidays/{holiday}', [HolidayController::class, 'destroy'])->name('calendar.holidays.destroy');

        // ── Master Data (Jabatan, Departemen, Kategori Karyawan) ──
        Route::get('master-data', [MasterDataController::class, 'index'])->name('master-data.index');
        Route::post('master-data/positions',                  [MasterDataController::class, 'storePosition'])->name('master-data.positions.store');
        Route::put('master-data/positions/{position}',        [MasterDataController::class, 'updatePosition'])->name('master-data.positions.update');
        Route::delete('master-data/positions/{position}',     [MasterDataController::class, 'destroyPosition'])->name('master-data.positions.destroy');
        Route::post('master-data/departments',                [MasterDataController::class, 'storeDepartment'])->name('master-data.departments.store');
        Route::put('master-data/departments/{department}',    [MasterDataController::class, 'updateDepartment'])->name('master-data.departments.update');
        Route::delete('master-data/departments/{department}', [MasterDataController::class, 'destroyDepartment'])->name('master-data.departments.destroy');
        Route::post('master-data/first-parties',                  [MasterDataController::class, 'storeFirstParty'])->name('master-data.first-parties.store');
        Route::put('master-data/first-parties/{firstParty}',      [MasterDataController::class, 'updateFirstParty'])->name('master-data.first-parties.update');
        Route::delete('master-data/first-parties/{firstParty}',   [MasterDataController::class, 'destroyFirstParty'])->name('master-data.first-parties.destroy');

        // ── CMS (Branding & Logo Perusahaan) ──
        Route::get('cms', [CmsController::class, 'index'])->name('cms.index');
        Route::post('cms/app-branding', [CmsController::class, 'updateAppBranding'])->name('cms.app-branding.update');
        Route::post('cms/companies/{company}/logo', [CmsController::class, 'updateCompanyLogo'])->name('cms.companies.logo.update');
        Route::post('cms/contract-template', [CmsController::class, 'updateContractTemplate'])->name('cms.contract-template.update');
        Route::post('cms/repo-tokens',        [CmsController::class, 'updateRepoTokens'])->name('cms.repo-tokens.update');

        // ── B2B Module ──
        Route::get('b2b', [\App\Http\Controllers\B2bDashboardController::class, 'index'])->name('b2b.dashboard');

        Route::resource('clients',  ClientController::class)->except(['create','edit']);
        Route::resource('projects', ProjectController::class);
        Route::post('projects/{project}/links',               [\App\Http\Controllers\ProjectResourceController::class, 'storeLink'])->name('projects.links.store');
        Route::delete('projects/{project}/links/{link}',      [\App\Http\Controllers\ProjectResourceController::class, 'destroyLink'])->name('projects.links.destroy');
        Route::post('projects/{project}/files',               [\App\Http\Controllers\ProjectResourceController::class, 'storeFile'])->name('projects.files.store');
        Route::get('projects/{project}/files/{file}',         [\App\Http\Controllers\ProjectResourceController::class, 'showFile'])->name('projects.files.show');
        Route::delete('projects/{project}/files/{file}',      [\App\Http\Controllers\ProjectResourceController::class, 'destroyFile'])->name('projects.files.destroy');

        // ── Project Plan ───────────────────────────────────────────
        Route::get('project-plan',                                         [ProjectPlanController::class, 'index'])->name('project-plan.index');
        Route::get('project-plan/{project}',                               [ProjectPlanController::class, 'show'])->name('project-plan.show');
        Route::post('project-plan/{project}/members',                      [ProjectPlanController::class, 'addMember'])->name('project-plan.members.add');
        Route::patch('project-plan/{project}/members/{employee}',          [ProjectPlanController::class, 'updateMember'])->name('project-plan.members.update');
        Route::delete('project-plan/{project}/members/{employee}',         [ProjectPlanController::class, 'removeMember'])->name('project-plan.members.remove');

        Route::resource('quotations', QuotationController::class);
        Route::patch('quotations/{quotation}/status',     [QuotationController::class,'updateStatus'])->name('quotations.status');
        Route::post('quotations/{quotation}/send',        [QuotationController::class,'markSent'])->name('quotations.send');
        Route::post('quotations/{quotation}/convert',     [QuotationController::class,'convertToInvoice'])->name('quotations.convert');
        Route::get('quotations/{quotation}/pdf',          [QuotationController::class,'pdf'])->name('quotations.pdf');

        Route::resource('invoices', InvoiceController::class);
        Route::patch('invoices/{invoice}/status',         [InvoiceController::class,'updateStatus'])->name('invoices.status');
        Route::post('invoices/{invoice}/send',            [InvoiceController::class,'markSent'])->name('invoices.send');
        Route::post('invoices/{invoice}/payment',         [InvoiceController::class,'recordPayment'])->name('invoices.payment');
        Route::delete('invoices/{invoice}/payment/{payment}', [InvoiceController::class,'deletePayment'])->name('invoices.payment.delete');
        Route::get('invoices/{invoice}/pdf',              [InvoiceController::class,'pdf'])->name('invoices.pdf');

        Route::resource('bank-accounts', \App\Http\Controllers\BankAccountController::class)
            ->only(['index','store','update','destroy'])
            ->parameters(['bank-accounts' => 'bank_account']);

        // ── Leave Management ───────────────────────────────────
        Route::get('leaves',                       [LeaveController::class, 'index'])->name('leaves.index');
        Route::get('leaves/types',                 [LeaveController::class, 'leaveTypes'])->name('leaves.types');
        Route::post('leaves/types',                [LeaveController::class, 'storeLeaveType'])->name('leaves.types.store');
        Route::put('leaves/types/{leaveType}',     [LeaveController::class, 'updateLeaveType'])->name('leaves.types.update');
        Route::delete('leaves/types/{leaveType}',  [LeaveController::class, 'destroyLeaveType'])->name('leaves.types.destroy');
        Route::get('leaves/{leaveRequest}',        [LeaveController::class, 'show'])->name('leaves.show');
        Route::patch('leaves/{leaveRequest}/approve', [LeaveController::class, 'approve'])->name('leaves.approve');
        Route::patch('leaves/{leaveRequest}/reject',  [LeaveController::class, 'reject'])->name('leaves.reject');

        // ── Announcements ──────────────────────────────────────
        Route::resource('announcements', AnnouncementController::class);

        // ── Internal Requests (Permohonan) ─────────────────────
        Route::get('internal-requests',                          [InternalRequestController::class, 'index'])->name('internal-requests.index');
        Route::get('internal-requests/{internalRequest}',        [InternalRequestController::class, 'show'])->name('internal-requests.show');
        Route::patch('internal-requests/{internalRequest}/respond', [InternalRequestController::class, 'respond'])->name('internal-requests.respond');

        // ── Reimbursement (staff/approver) ─────────────────────
        Route::get('reimbursements', [ReimbursementController::class, 'index'])->name('reimbursements.index');
        Route::get('reimbursements/{reimbursement}', [ReimbursementController::class, 'show'])->name('reimbursements.show');
        Route::patch('reimbursements/{reimbursement}/approve', [ReimbursementController::class, 'approve'])->name('reimbursements.approve');
        Route::patch('reimbursements/{reimbursement}/reject',  [ReimbursementController::class, 'reject'])->name('reimbursements.reject');
        Route::delete('reimbursements/{reimbursement}',        [ReimbursementController::class, 'destroy'])->name('reimbursements.destroy');
        Route::get('reimbursements/{reimbursement}/transfer-proof', [ReimbursementController::class, 'showTransferProof'])->name('reimbursements.transfer-proof');
        Route::get('reimbursements/{reimbursement}/documents/{document}', [ReimbursementController::class, 'showDocument'])->name('reimbursements.documents.show');

    }); // end not-employee

});
