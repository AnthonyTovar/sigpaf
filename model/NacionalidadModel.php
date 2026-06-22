<?php
require_once 'ConexionModel.php';

class NacionalidadModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Genera la secuencia alfanumérica NA0001, NA0002...
     */
    private function generarNuevoId()
    {
        $sql = "SELECT idNacionalidad FROM nacionalidad ORDER BY idNacionalidad DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "NA0001";
        }

        $numero = substr($ultimoId, 2);
        $nuevoNumero = intval($numero) + 1;

        return "NA" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
    }

    public function listarNacionalidades()
    {
        $sql = "SELECT * FROM nacionalidad ORDER BY idNacionalidad ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarNacionalidad($nombre)
    {
        $nuevoId = $this->generarNuevoId();

        $sql = "INSERT INTO nacionalidad (idNacionalidad, nomNacionalidad) 
            VALUES (:id, :nombre)";
        $stmt = $this->db->prepare($sql);

        $resultado = $stmt->execute([
            'id' => $nuevoId,
            'nombre' => $nombre
        ]);

        return $resultado ? $nuevoId : false;
    }

    public function eliminarNacionalidad($id)
    {
        try {
            $sql = "DELETE FROM nacionalidad WHERE idNacionalidad = ?";
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

    public function obtenerNacionalidadPorId($id)
    {
        $sql = "SELECT * FROM nacionalidad WHERE idNacionalidad = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarNacionalidad($id, $nombre)
    {
        $sql = "UPDATE nacionalidad SET nomNacionalidad = :nombre WHERE idNacionalidad = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nombre' => $nombre
        ]);
    }
}