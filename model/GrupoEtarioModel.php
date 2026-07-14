<?php
require_once 'ConexionModel.php';

class GrupoEtarioModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function generarNuevoId()
    {
        $sql = "SELECT idGrupoEtareo FROM grupoEtario ORDER BY idGrupoEtareo DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "GE0001";
        }

        $numero = substr($ultimoId, 2);
        $nuevoNumero = intval($numero) + 1;

        return "GE" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
    }

    public function listarGrupos()
    {
        $sql = "SELECT idGrupoEtareo, nomGrupoEtareo, edadMin, edadMax, descGrupoEtareo 
                FROM grupoEtario 
                ORDER BY idGrupoEtareo ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarGrupo($nombre, $edadMin, $edadMax, $descripcion)
    {
        $nuevoId = $this->generarNuevoId();

        $sql = "INSERT INTO grupoEtario (idGrupoEtareo, nomGrupoEtareo, edadMin, edadMax, descGrupoEtareo) 
                VALUES (:id, :nombre, :edadMin, :edadMax, :descripcion)";
        $stmt = $this->db->prepare($sql);

        $resultado = $stmt->execute([
            'id' => $nuevoId,
            'nombre' => $nombre,
            'edadMin' => $edadMin,
            'edadMax' => $edadMax,
            'descripcion' => $descripcion
        ]);

        return $resultado ? $nuevoId : false;
    }

    public function eliminarGrupo($id)
    {
        try {
            $sql = "DELETE FROM grupoEtario WHERE idGrupoEtareo = ?";
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

    public function obtenerGrupoPorId($id)
    {
        $sql = "SELECT idGrupoEtareo, nomGrupoEtareo, edadMin, edadMax, descGrupoEtareo 
                FROM grupoEtario 
                WHERE idGrupoEtareo = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarGrupo($id, $nombre, $edadMin, $edadMax, $descripcion)
    {
        $sql = "UPDATE grupoEtario 
                SET nomGrupoEtareo = :nombre, edadMin = :edadMin, edadMax = :edadMax, descGrupoEtareo = :descripcion 
                WHERE idGrupoEtareo = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'nombre' => $nombre,
            'edadMin' => $edadMin,
            'edadMax' => $edadMax,
            'descripcion' => $descripcion
        ]);
    }
}