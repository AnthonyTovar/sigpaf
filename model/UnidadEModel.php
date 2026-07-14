<?php
require_once 'ConexionModel.php';

class UnidadEModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Genera la secuencia alfanumérica CR0001, CR0002...
     */
    private function generarNuevoId()
    {
        $sql = "SELECT idUnidadEjecutora FROM unidadEjecutora ORDER BY idUnidadEjecutora DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "UNE0001";
        }

        // Quitamos los tres primeros caracteres ("Dep") y sumamos 1
        $numero = substr($ultimoId, 3);
        $nuevoNumero = intval($numero) + 1;

        return "UNE" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
    }

    public function listarUnidadE()
    {
        $sql = "SELECT * FROM unidadEjecutora ORDER BY idUnidadEjecutora ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarUnidadE($nombre, $descripcion)
    {
        // 1. Genera el ID
        $nuevoId = $this->generarNuevoId();

        $sql = "INSERT INTO unidadEjecutora (idUnidadEjecutora, nomUnidadEjecutora, desUnidadEjecutora) 
            VALUES (:id, :nombre, :desc)";
        $stmt = $this->db->prepare($sql);

        $resultado = $stmt->execute([
            'id' => $nuevoId,
            'nombre' => $nombre,
            'desc' => $descripcion
        ]);

        // 2. devolver true/false
        if ($resultado) {
            return $nuevoId;
        } else {
            return false;
        }
    }

    public function eliminarUnidadE($id)
    {
        try {
            $sql = "DELETE FROM unidadEjecutora WHERE idUnidadEjecutora = ?";
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

    public function obtenerUnidadEPorId($id)
    {
        $sql = "SELECT * FROM unidadEjecutora WHERE idUnidadEjecutora = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarUnidadE($id, $nombre, $desc)
    {
        $sql = "UPDATE unidadEjecutora SET nomUnidadEjecutora = :nombre, desUnidadEjecutora = :desc WHERE idUnidadEjecutora = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nombre' => $nombre,
            'desc' => $desc
        ]);
    }
}