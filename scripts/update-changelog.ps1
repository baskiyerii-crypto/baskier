param(
    [string]$ChangelogPath = "CHANGELOG.md",
    [int]$MaxFiles = 8
)

$ErrorActionPreference = "Stop"

try {
    $changedFiles = git diff --cached --name-only --diff-filter=ACMR |
        Where-Object { $_ -and $_ -ne $ChangelogPath } |
        Select-Object -First $MaxFiles

    if (-not $changedFiles -or $changedFiles.Count -eq 0) {
        exit 0
    }

    if (-not (Test-Path $ChangelogPath)) {
        Set-Content -Path $ChangelogPath -Value "# PROJE CHANGELOG`r`n" -Encoding UTF8
    }

    $existingContent = Get-Content -Path $ChangelogPath -Raw -Encoding UTF8
    if ([string]::IsNullOrWhiteSpace($existingContent)) {
        $existingContent = "# PROJE CHANGELOG`r`n"
    }

    $now = Get-Date -Format "yyyy-MM-dd HH:mm"
    $entryLines = @(
        "",
        "---",
        "",
        "## [AUTO] [$now] Commit Oncesi Kayit",
        "### Degisen Dosyalar"
    )

    foreach ($file in $changedFiles) {
        $entryLines += "- ``$file``"
    }

    $entryLines += @(
        "",
        "### Not",
        "- Bu kayit pre-commit hook tarafindan otomatik eklendi."
    )

    $entry = ($entryLines -join "`r`n")
    Set-Content -Path $ChangelogPath -Value ($existingContent.TrimEnd() + "`r`n" + $entry + "`r`n") -Encoding UTF8
}
catch {
    Write-Error "CHANGELOG otomatik güncellenemedi: $($_.Exception.Message)"
    exit 1
}
