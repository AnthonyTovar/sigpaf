<?php
require_once 'ConexionModel.php';

class DocenteModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function generarNuevoId()
    {
        $sql = "SELECT idDocente FROM docente ORDER BY idDocente DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "DC0001";
        }

        $numero = substr($ultimoId, 2);
        $nuevoNumero = intval($numero) + 1;

        return "DC" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
    }

    public function existeCedula($cedula, $excluirId = null)
    {
        if ($excluirId) {
            $sql = "SELECT COUNT(*) FROM docente WHERE cedDocente = :cedula AND idDocente != :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['cedula' => $cedula, 'id' => $excluirId]);
        } else {
            $sql = "SELECT COUNT(*) FROM docente WHERE cedDocente = :cedula";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['cedula' => $cedula]);
        }
        return $stmt->fetchColumn() > 0;
    }

    public function listarDocentes()
    {
        $sql = "SELECT * FROM docente ORDER BY idDocente ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function registrarDocente($prefijoNacionalidad, $cedula, $nombres, $apellidos, $telefono)
    {
        $nacionalidad = ($prefijoNacionalidad === 'E') ? 'Extranjero' : 'Venezolano';

        // Verificar si la cédula ya existe
        if ($this->existeCedula($cedula)) {
            return 'cedula_duplicada';
        }

        $nuevoId = $this->generarNuevoId();

        $sql = "INSERT INTO docente (idDocente, nacionalidad, cedDocente, nombreDocente, apellidoDocente, telfDocente) 
                VALUES (:id, :nacionalidad, :cedula, :nombres, :apellidos, :telefono)";
        $stmt = $this->db->prepare($sql);

        $resultado = $stmt->execute([
            'id' => $nuevoId,
            'nacionalidad' => $nacionalidad,
            'cedula' => $cedula,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'telefono' => $telefono
        ]);

        return $resultado ? $nuevoId : false;
    }

    public function eliminarDocente($id)
    {
        try {
            $sql = "DELETE FROM docente WHERE idDocente = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);

            return ($stmt->rowCount() > 0);

        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                return "link";
            }
            return false;
        }
    }

    public function obtenerDocentePorId($id)
    {
        $sql = "SELECT * FROM docente WHERE idDocente = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarDocente($id, $prefijoNacionalidad, $cedula, $nombres, $apellidos, $telefono)
    {
        $nacionalidad = ($prefijoNacionalidad === 'E') ? 'Extranjero' : 'Venezolano';

        // Verificar si la cédula ya existe en OTRO docente
        if ($this->existeCedula($cedula, $id)) {
            return 'cedula_duplicada';
        }

        $sql = "UPDATE docente 
                SET nacionalidad = :nacionalidad, cedDocente = :cedula, nombreDocente = :nombres, 
                    apellidoDocente = :apellidos, telfDocente = :telefono
                WHERE idDocente = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nacionalidad' => $nacionalidad,
            'cedula' => $cedula,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'telefono' => $telefono
        ]);
    }
}