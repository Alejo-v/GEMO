    <?php
use PHPUnit\Framework\TestCase;

class TerrenoTest extends TestCase
{
    private $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO("pgsql:host=localhost;port=5432;dbname=bd_gemo", "postgres", "kevin1913");
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // p10 rgistrar seguimiento de terreno válido
    public function testRegistrarSeguimientoTerreno()
    {
        $datos = [
            ':id_deposito' => 1,          //ya existe
            ':id_usuario' => 5,           //alejandro - aux terreno
            ':fecha' => date('Y-m-d'),    //hoy
            ':id_actividad_terreno' => 1, //siembra
            ':ph' => 7.2,
            ':temperatura' => 28.5,
            ':larvas_aedes' => 10,
            ':pupas' => 5,
            ':larvas_culex' => 2
        ];

        $sql = "INSERT INTO seguimiento_terreno 
                (id_deposito, id_usuario, fecha, id_actividad_terreno, ph, temperatura, larvas_aedes, pupas, larvas_culex) 
                VALUES 
                (:id_deposito, :id_usuario, :fecha, :id_actividad_terreno, :ph, :temperatura, :larvas_aedes, :pupas, :larvas_culex)";
        
        $stmt = $this->pdo->prepare($sql);
        $resultado = $stmt->execute($datos);


        $this->assertTrue($resultado, "El seguimiento epidemiológico en terreno debe guardarse correctamente.");
    }
}