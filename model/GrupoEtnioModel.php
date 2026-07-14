<?php
require_once 'ConexionModel.php';

class GrupoEtnioModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function generarNuevoId()
    {
        $sql = "SELECT idGrupoEtnio FROM grupoEtnio ORDER BY idGrupoEtnio DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "GN0001";
        }

        $numero = substr($ultimoId, 2);
        $nuevoNumero = intval($numero) + 1;

        return "GN" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
    }

    public function listarGrupos()
    {
        $sql = "SELECT idGrupoEtnio, nomGrupoEtnio, desGrupoEtnio 
                FROM grupoEtnio 
                ORDER BY idGrupoEtnio ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarGrupo($nombre, $descripcion)
    {
        $nuevoId = $this->generarNuevoId();

        $sql = "INSERT INTO grupoEtnio (idGrupoEtnio, nomGrupoEtnio, desGrupoEtnio) 
                VALUES (:id, :nombre, :descripcion)";
        $stmt = $this->db->prepare($sql);

        $resultado = $stmt->execute([
            'id' => $nuevoId,
            'nombre' => $nombre,
            'descripcion' => $descripcion
        ]);

        return $resultado ? $nuevoId : false;
    }

    public function eliminarGrupo($id)
    {
        try {
            $sql = "DELETE FROM grupoEtnio WHERE idGrupoEtnio = ?";
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
        $sql = "SELECT idGrupoEtnio, nomGrupoEtnio, desGrupoEtnio 
                FROM grupoEtnio 
                WHERE idGrupoEtnio = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarGrupo($id, $nombre, $descripcion)
    {
        $sql = "UPDATE grupoEtnio 
                SET nomGrupoEtnio = :nombre, desGrupoEtnio = :descripcion 
                WHERE idGrupoEtnio = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'nombre' => $nombre,
            'descripcion' => $descripcion
        ]);
    }
}