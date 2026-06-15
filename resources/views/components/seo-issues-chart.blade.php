@props(['critical', 'error', 'warning', 'info'])

<div class="card-dashboard h-100">
    <div class="card-header">
        <h5 class="mb-0" style="font-size: 0.875rem; font-weight: 700; color: #111827;">Issue Distribution</h5>
    </div>
    <div class="card-body">
        <div id="issuesPieChart" 
             data-critical="{{ $critical }}" 
             data-error="{{ $error }}" 
             data-warning="{{ $warning }}" 
             data-info="{{ $info }}" 
             style="width: 100%; height: 220px;"></div>
    </div>
</div>
