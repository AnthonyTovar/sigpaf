<?php
require_once 'ConexionModel.php';

class ParroquiaModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Genera la secuencia alfanumérica PR0001, PR0002...
     */
    private function generarNuevoId()
    {
        $sql = "SELECT idParroquia FROM parroquia ORDER BY idParroquia DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "PR0001";
        }

        $numero = substr($ultimoId, 2);
        $nuevoNumero = intval($numero) + 1;

        return "PR" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
    }

    public function listarParroquias()
    {
        $sql = "SELECT p.*, m.nombreMunicipio 
                FROM parroquia p 
                LEFT JOIN municipio m ON p.idMunicipio = m.idMunicipio 
                ORDER BY p.idParroquia ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarMunicipios()
    {
        $sql = "SELECT idMunicipio, nombreMunicipio FROM municipio ORDER BY nombreMunicipio ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarParroquia($nombre, $idMunicipio)
    {
        $nuevoId = $this->generarNuevoId();

        $sql = "INSERT INTO parroquia (idParroquia, nombreParroquia, idMunicipio) 
            VALUES (:id, :nombre, :idMunicipio)";
        $stmt = $this->db->prepare($sql);

        $resultado = $stmt->execute([
            'id' => $nuevoId,
            'nombre' => $nombre,
            'idMunicipio' => $idMunicipio
        ]);

        return $resultado ? $nuevoId : false;
    }

    public function eliminarParroquia($id)
    {
        try {
            $sql = "DELETE FROM parroquia WHERE idParroquia = ?";
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

    public function obtenerParroquiaPorId($id)
    {
        $sql = "SELECT p.*, m.nombreMunicipio 
                FROM parroquia p 
                LEFT JOIN municipio m ON p.idMunicipio = m.idMunicipio 
                WHERE p.idParroquia = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarParroquia($id, $nombre, $idMunicipio)
    {
        $sql = "UPDATE parroquia 
                SET nombreParroquia = :nombre, idMunicipio = :idMunicipio 
                WHERE idParroquia = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nombre' => $nombre,
            'idMunicipio' => $idMunicipio
        ]);
    }
}