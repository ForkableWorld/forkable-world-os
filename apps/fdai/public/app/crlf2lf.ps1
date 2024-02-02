$extensions = @('.md','.js','.html','.htm','.xhtml','.css')
$path = Split-Path -Parent $MyInvocation.MyCommand.Path

Get-ChildItem -Path $path -Recurse -Exclude node_modules | Where-Object {!$_.PSIsContainer -and $extensions.Contains($_.Extension) -and $_.FullName -notlike "$path\node_modules\*"} | ForEach-Object {
    $filePath = $_.FullName
    $content = Get-Content $filePath -Raw
    if ($content.Contains("`r`n")) {
        Write-Host "Converting file '$filePath' to LF line endings..."
        $content = $content -replace "`r`n", "`n"
        $content | Set-Content -NoNewline $filePath -Encoding ASCII
    }
    else {
        Write-Host "Skipping file '$filePath' because it already has LF line endings."
    }
}

Write-Host "Done!"
