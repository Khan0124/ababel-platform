Invoke-WebRequest -UseBasicParsing "https://desktop.docker.com/win/main/amd64/Docker Desktop Installer.exe" -OutFile "$env:USERPROFILE\Downloads\DockerInstaller.exe"
Start-Process -FilePath "$env:USERPROFILE\Downloads\DockerInstaller.exe" -Wait
