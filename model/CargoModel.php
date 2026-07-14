<?php
require_once 'ConexionModel.php';

class CargoModel
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
        $sql = "SELECT idCargo FROM cargo ORDER BY idCargo DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "CR0001";
        }

        $numero = substr($ultimoId, 2);
        $nuevoNumero = intval($numero) + 1;

        return "CR" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
    }

    public function listarCargos()
    {
        $sql = "SELECT * FROM cargo ORDER BY idCargo ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarCargo($nombre, $descripcion)
    {
        // Generamos el ID automáticamente
        $nuevoId = $this->generarNuevoId();

        $sql = "INSERT INTO cargo (idCargo, nombreCargo, descripcionCargo) 
            VALUES (:id, :nombre, :desc)";
        $stmt = $this->db->prepare($sql);

        $resultado = $stmt->execute([
            'id' => $nuevoId,
            'nombre' => $nombre,
            'desc' => $descripcion
        ]);

        return $resultado ? $nuevoId : false;
    }

    public function eliminarCargo($id)
    {
        try {
            $sql = "DELETE FROM cargo WHERE idCargo = ?";
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

    public function obtenerCargoPorId($id)
    {
        $sql = "SELECT * FROM cargo WHERE idCargo = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarCargo($id, $nombre, $desc)
    {
        $sql = "UPDATE cargo SET nombreCargo = :nombre, descripcionCargo = :desc WHERE idCargo = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nombre' => $nombre,
            'desc' => $desc
        ]);
    }
}