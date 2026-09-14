GEMO - FASE 1

Tecnologías:
- PHP 8+
- PostgreSQL
- PDO PostgreSQL
- Bootstrap 5 / KaiAdmin Lite

INSTALACIÓN
1. Instale PHP con el driver PostgreSQL (pdo_pgsql), PostgreSQL y un servidor web como Apache.
2. Cree una base de datos llamada: gemo
3. Importe sql/bd_gemo.sql en esa base de datos.
4. Abra config/database.php y cambie:
   - username
   - password
   si su instalación de PostgreSQL usa otros datos.
5. Coloque la carpeta GEMO dentro del directorio público de su servidor.
6. Abra: http://localhost/GEMO/login.php

USUARIO INICIAL
Correo: admin@gemo.local
Contraseña: Admin123!

IMPORTANTE: cambie esta contraseña después del primer acceso. Es una credencial de desarrollo.

FASE 1 INCLUYE
- Identidad visual GEMO basada en KaiAdmin Lite.
- Login con consulta preparada y password_verify().
- Sesiones PHP.
- Control de acceso por rol.
- Dashboard de Administrador.
- Listado de usuarios, con edición de apellido, correo, contraseña y rol.
- Registro de usuarios.
- Validaciones de correo, cédula, campos obligatorios y contraseñas.
- password_hash() para almacenar contraseñas.
- Los seis roles definidos en la base de datos.
- Página de inicio (index.php) con diseño KaiAdmin para los roles que aún
  no tienen módulo propio.
- Reportes del zoocriadero para el Administrador (views/admin/reportes.php):
  filtro por rango de fechas, tarjetas de resumen, gráficos de peces vivos
  vs. muertos con Chart.js (ya incluido en assets/js/plugin/chart.js) y
  descarga del reporte en PDF.
- Reportes de terreno para el Auxiliar de Terreno (views/auxiliar_terreno/reportes.php):
  los 4 reportes del proceso de Trabajo de Terreno, con filtros por comuna,
  barrio, tipo de depósito y fechas (models/ReporteTerreno.php).

DEPENDENCIA PARA LOS REPORTES EN PDF
La generación de PDF usa la librería FPDF (lib/fpdf/), incluida como
archivos PHP planos (sin Composer). Requiere que la extensión "iconv" de
PHP esté habilitada (viene activada por defecto en casi todas las
instalaciones) para que los acentos y la "ñ" se muestren correctamente.

NOTA
Los demás roles pueden autenticarse y acceden a index.php con su rol, pero sus módulos funcionales se desarrollarán en fases posteriores.
