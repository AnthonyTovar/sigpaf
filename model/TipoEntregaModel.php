<?php
require_once 'ConexionModel.php';

class TipoEntregaModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function generarNuevoId()
    {
        $sql = "SELECT idTipEntrega FROM tipoEntrega ORDER BY idTipEntrega DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "TE0001";
        }

        $numero = substr($ultimoId, 2);
        $nuevoNumero = intval($numero) + 1;

        return "TE" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
    }

    public function listarTiposEntrega()
    {
        $sql = "SELECT * FROM tipoEntrega ORDER BY idTipEntrega ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarTipoEntrega($nomTipEntrega)
    {
        if ($this->existeNombre($nomTipEntrega)) {
            return 'nombre_duplicado';
        }

        $nuevoId = $this->generarNuevoId();

        $sql = "INSERT INTO tipoEntrega (idTipEntrega, nomTipEntrega) VALUES (:id, :nomTipEntrega)";
        $stmt = $this->db->prepare($sql);

        $resultado = $stmt->execute([
            'id' => $nuevoId,
            'nomTipEntrega' => $nomTipEntrega
        ]);

        return $resultado ? $nuevoId : false;
    }

    public function eliminarTipoEntrega($id)
    {
        try {
            $sql = "DELETE FROM tipoEntrega WHERE idTipEntrega = ?";
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

    public function obtenerTipoEntregaPorId($id)
    {
        $sql = "SELECT * FROM tipoEntrega WHERE idTipEntrega = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarTipoEntrega($id, $nomTipEntrega)
    {
        if ($this->existeNombre($nomTipEntrega, $id)) {
            return 'nombre_duplicado';
        }

        $sql = "UPDATE tipoEntrega SET nomTipEntrega = :nomTipEntrega WHERE idTipEntrega = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nomTipEntrega' => $nomTipEntrega
        ]);
    }

    public function existeNombre($nomTipEntrega, $excluirId = null)
    {
        if ($excluirId) {
            $sql = "SELECT COUNT(*) FROM tipoEntrega WHERE nomTipEntrega = ? AND idTipEntrega != ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nomTipEntrega, $excluirId]);
        } else {
            $sql = "SELECT COUNT(*) FROM tipoEntrega WHERE nomTipEntrega = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nomTipEntrega]);
        }
        return $stmt->fetchColumn() > 0;
    }
}