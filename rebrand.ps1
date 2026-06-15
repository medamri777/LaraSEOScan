Get-ChildItem -Path 'c:\Users\youcc\Desktop\LaraSEOScan\frontend\app' -Recurse -Filter '*.tsx' | ForEach-Object {
    $content = Get-Content $_.FullName -Raw
    if ($content -match 'NorthRank') {
        $content -replace 'NorthRank', 'SeoBladi' | Set-Content $_.FullName -NoNewline
        Write-Host "Updated: $($_.FullName)"
    }
}

# Also update paypalClient.ts
$paypalPath = 'c:\Users\youcc\Desktop\LaraSEOScan\frontend\lib\paypalClient.ts'
if (Test-Path $paypalPath) {
    $content = Get-Content $paypalPath -Raw
    $content -replace 'NorthRank', 'SeoBladi' | Set-Content $paypalPath -NoNewline
    Write-Host "Updated: paypalClient.ts"
}

# Update backend PHP files
$phpFiles = @(
    'c:\Users\youcc\Desktop\LaraSEOScan\config\mail.php',
    'c:\Users\youcc\Desktop\LaraSEOScan\routes\console.php',
    'c:\Users\youcc\Desktop\LaraSEOScan\app\Support\PlanLimits.php',
    'c:\Users\youcc\Desktop\LaraSEOScan\app\Providers\Filament\AdminPanelProvider.php',
    'c:\Users\youcc\Desktop\LaraSEOScan\app\Mail\WorkspaceInvitationMail.php',
    'c:\Users\youcc\Desktop\LaraSEOScan\resources\views\exports\scan-pdf.blade.php',
    'c:\Users\youcc\Desktop\LaraSEOScan\resources\views\emails\workspace-invitation.blade.php'
)

foreach ($f in $phpFiles) {
    if (Test-Path $f) {
        $content = Get-Content $f -Raw
        $content -replace 'NorthRank', 'SeoBladi' -replace 'MaghrebSEO', 'SeoBladi' -replace 'northrank\.io', 'seobladi.ma' | Set-Content $f -NoNewline
        Write-Host "Updated: $f"
    }
}

Write-Host "`nDone! All NorthRank references replaced with SeoBladi."
