<?php
require_once 'ConexionModel.php';

class EstrategiaDesarrolloModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function generarNuevoId()
    {
        $sql = "SELECT idEstDesarrollo FROM estrategiaDesarrollo ORDER BY idEstDesarrollo DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "ED0001";
        }

        $numero = substr($ultimoId, 2);
        $nuevoNumero = intval($numero) + 1;

        return "ED" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
    }

    public function listarEstrategias()
    {
        $sql = "SELECT * FROM estrategiaDesarrollo ORDER BY idEstDesarrollo ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarEstrategia($nomEstDesarrollo, $descEstDesarrollo)
    {
        if ($this->existeNombre($nomEstDesarrollo)) {
            return 'nombre_duplicado';
        }

        $nuevoId = $this->generarNuevoId();

        $sql = "INSERT INTO estrategiaDesarrollo (idEstDesarrollo, nomEstDesarrollo, descEstDesarrollo) 
                VALUES (:id, :nomEstDesarrollo, :descEstDesarrollo)";
        $stmt = $this->db->prepare($sql);

        $resultado = $stmt->execute([
            'id' => $nuevoId,
            'nomEstDesarrollo' => $nomEstDesarrollo,
            'descEstDesarrollo' => $descEstDesarrollo
        ]);

        return $resultado ? $nuevoId : false;
    }

    public function eliminarEstrategia($id)
    {
        try {
            $sql = "DELETE FROM estrategiaDesarrollo WHERE idEstDesarrollo = ?";
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

    public function obtenerEstrategiaPorId($id)
    {
        $sql = "SELECT * FROM estrategiaDesarrollo WHERE idEstDesarrollo = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarEstrategia($id, $nomEstDesarrollo, $descEstDesarrollo)
    {
        if ($this->existeNombre($nomEstDesarrollo, $id)) {
            return 'nombre_duplicado';
        }

        $sql = "UPDATE estrategiaDesarrollo 
                SET nomEstDesarrollo = :nomEstDesarrollo, descEstDesarrollo = :descEstDesarrollo 
                WHERE idEstDesarrollo = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nomEstDesarrollo' => $nomEstDesarrollo,
            'descEstDesarrollo' => $descEstDesarrollo
        ]);
    }

    public function existeNombre($nomEstDesarrollo, $excluirId = null)
    {
        if ($excluirId) {
            $sql = "SELECT COUNT(*) FROM estrategiaDesarrollo WHERE nomEstDesarrollo = ? AND idEstDesarrollo != ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nomEstDesarrollo, $excluirId]);
        } else {
            $sql = "SELECT COUNT(*) FROM estrategiaDesarrollo WHERE nomEstDesarrollo = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nomEstDesarrollo]);
        }
        return $stmt->fetchColumn() > 0;
    }
}