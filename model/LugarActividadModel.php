<?php
require_once 'ConexionModel.php';

class LugarActividadModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function generarNuevoId()
    {
        $sql = "SELECT idLugarActividad FROM lugarActividad ORDER BY idLugarActividad DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "LA0001";
        }

        $numero = substr($ultimoId, 2);
        $nuevoNumero = intval($numero) + 1;

        return "LA" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
    }

    public function listarLugares()
    {
        $sql = "SELECT l.*, p.nombreParroquia, m.nombreMunicipio, e.nombreEstado 
                FROM lugarActividad l 
                LEFT JOIN parroquia p ON l.idParroquia = p.idParroquia 
                LEFT JOIN municipio m ON p.idMunicipio = m.idMunicipio 
                LEFT JOIN estado e ON m.idEstado = e.idEstado 
                ORDER BY l.idLugarActividad ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarParroquias()
    {
        $sql = "SELECT p.idParroquia, p.nombreParroquia, m.nombreMunicipio, e.nombreEstado 
                FROM parroquia p 
                LEFT JOIN municipio m ON p.idMunicipio = m.idMunicipio 
                LEFT JOIN estado e ON m.idEstado = e.idEstado 
                ORDER BY e.nombreEstado, m.nombreMunicipio, p.nombreParroquia ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarLugar($nomLugar, $desLugar, $direccion, $esSede, $idParroquia)
    {
        if ($this->verificarNombreLugar($nomLugar)) {
            return 'nombre_duplicado';
        }

        $nuevoId = $this->generarNuevoId();

        $sql = "INSERT INTO lugarActividad (idLugarActividad, nomLugarActividad, desLugarActividad, direccion, esSede, idParroquia) 
                VALUES (:id, :nomLugar, :desLugar, :direccion, :esSede, :idParroquia)";
        $stmt = $this->db->prepare($sql);

        $resultado = $stmt->execute([
            'id' => $nuevoId,
            'nomLugar' => $nomLugar,
            'desLugar' => $desLugar,
            'direccion' => $direccion,
            'esSede' => $esSede ? 1 : 0,
            'idParroquia' => $idParroquia
        ]);

        return $resultado ? $nuevoId : false;
    }

    public function eliminarLugar($id)
    {
        try {
            $sql = "DELETE FROM lugarActividad WHERE idLugarActividad = ?";
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

    public function obtenerLugarPorId($id)
    {
        $sql = "SELECT l.*, p.nombreParroquia, m.nombreMunicipio, e.nombreEstado 
                FROM lugarActividad l 
                LEFT JOIN parroquia p ON l.idParroquia = p.idParroquia 
                LEFT JOIN municipio m ON p.idMunicipio = m.idMunicipio 
                LEFT JOIN estado e ON m.idEstado = e.idEstado 
                WHERE l.idLugarActividad = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarLugar($id, $nomLugar, $desLugar, $direccion, $esSede, $idParroquia)
    {
        if ($this->verificarNombreLugar($nomLugar, $id)) {
            return 'nombre_duplicado';
        }

        $sql = "UPDATE lugarActividad 
                SET nomLugarActividad = :nomLugar, desLugarActividad = :desLugar, 
                    direccion = :direccion, esSede = :esSede, idParroquia = :idParroquia 
                WHERE idLugarActividad = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nomLugar' => $nomLugar,
            'desLugar' => $desLugar,
            'direccion' => $direccion,
            'esSede' => $esSede ? 1 : 0,
            'idParroquia' => $idParroquia
        ]);
    }

    public function verificarNombreLugar($nomLugar, $excluirId = null)
    {
        if ($excluirId) {
            $sql = "SELECT COUNT(*) FROM lugarActividad WHERE nomLugarActividad = ? AND idLugarActividad != ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nomLugar, $excluirId]);
        } else {
            $sql = "SELECT COUNT(*) FROM lugarActividad WHERE nomLugarActividad = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nomLugar]);
        }
        return $stmt->fetchColumn() > 0;
    }
}