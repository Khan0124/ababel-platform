@echo off
cd C:\flutter_projects\nokta_saas
dart pub global activate melos
melos bootstrap
cd packages\core
flutter pub get
cd ..\..
dart fix --apply
flutter analyze
pause
