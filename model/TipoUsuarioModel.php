<?php
require_once 'ConexionModel.php';

class TipoUsuarioModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function generarNuevoId()
    {
        $sql = "SELECT idTipoUsuario FROM tipoUsuario ORDER BY idTipoUsuario DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "TU0001";
        }

        $numero = substr($ultimoId, 2);
        $nuevoNumero = intval($numero) + 1;

        return "TU" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
    }

    public function listarTipoUsuarios()
    {
        $sql = "SELECT * FROM tipoUsuario ORDER BY idTipoUsuario ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarTipoUsuario($rolUsuario)
    {
        $nuevoId = $this->generarNuevoId();

        $sql = "INSERT INTO tipoUsuario (idTipoUsuario, rolUsuario) VALUES (:id, :rol)";
        $stmt = $this->db->prepare($sql);

        $resultado = $stmt->execute([
            'id' => $nuevoId,
            'rol' => $rolUsuario
        ]);

        return $resultado ? $nuevoId : false;
    }

    public function eliminarTipoUsuario($id)
    {
        try {
            $sql = "DELETE FROM tipoUsuario WHERE idTipoUsuario = ?";
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

    public function obtenerTipoUsuarioPorId($id)
    {
        $sql = "SELECT * FROM tipoUsuario WHERE idTipoUsuario = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarTipoUsuario($id, $rolUsuario)
    {
        $sql = "UPDATE tipoUsuario SET rolUsuario = :rol WHERE idTipoUsuario = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'rol' => $rolUsuario
        ]);
    }
}