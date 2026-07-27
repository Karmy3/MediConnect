# Tue tout processus php.exe qui ecoute sur le port 8000 (conflit avec Docker)
$connexions = netstat -ano | Select-String ":8000" | Select-String "LISTENING"

foreach ($ligne in $connexions) {
    $colonnes = ($ligne -split '\s+') | Where-Object { $_ -ne "" }
    $pid_trouve = $colonnes[-1]
    $adresse = $colonnes[1]

    # On ne touche pas au PID de Docker (0.0.0.0:8000), seulement 127.0.0.1:8000
    if ($adresse -like "127.0.0.1:8000*") {
        $processus = Get-Process -Id $pid_trouve -ErrorAction SilentlyContinue
        if ($processus -and $processus.ProcessName -eq "php") {
            Write-Host "Fermeture d'un ancien serveur PHP local (PID $pid_trouve)..."
            Stop-Process -Id $pid_trouve -Force
        }
    }
}

Write-Host "Demarrage de Docker..."
docker compose up -d

Write-Host ""
Write-Host "Pret ! Frontend : http://localhost:5173"
Write-Host "Backend : http://localhost:8000/api"