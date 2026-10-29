<?php
// Modelo de unidades ejecutoras



require_once 'ConexionModel.php';

class UnidadEModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }


    private function generarNuevoId()
    {
        $sql = "SELECT idUnidadEjecutora FROM unidadejecutora ORDER BY idUnidadEjecutora DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "UNE0001";
        }

        $numero = substr($ultimoId, 3);
        $nuevoNumero = intval($numero) + 1;

        return "UNE" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
    }


    public function existeNombreODescripcion($nombre, $descripcion, $idIgnorar = null)
    {
        $nombreTrim = trim($nombre);
        $descTrim = trim($descripcion);


        $evaluarDesc = (!empty($descTrim) && strtoupper($descTrim) !== 'N/A');

        $sql = "SELECT nomUnidadEjecutora, desUnidadEjecutora 
                FROM unidadejecutora 
                WHERE (LOWER(nomUnidadEjecutora) = LOWER(:nombre)";

        if ($evaluarDesc) {
            $sql .= " OR LOWER(desUnidadEjecutora) = LOWER(:desc)";
        }
        $sql .= ")";

        if ($idIgnorar) {
            $sql .= " AND idUnidadEjecutora != :idIgnorar";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nombre', $nombreTrim);
        if ($evaluarDesc) {
            $stmt->bindValue(':desc', $descTrim);
        }
        if ($idIgnorar) {
            $stmt->bindValue(':idIgnorar', $idIgnorar);
        }

        $stmt->execute();
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($registros)) {
            return false;
        }


        foreach ($registros as $reg) {
            if (strtolower(trim($reg['nomUnidadEjecutora'])) === strtolower($nombreTrim)) {
                return 'nombre';
            }
            if ($evaluarDesc && strtolower(trim($reg['desUnidadEjecutora'])) === strtolower($descTrim)) {
                return 'descripcion';
            }
        }

        return false;
    }

    public function listarUnidadE()
    {
        $sql = "SELECT * FROM unidadejecutora ORDER BY idUnidadEjecutora ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarUnidadE($nombre, $descripcion)
    {
        $nuevoId = $this->generarNuevoId();

        $sql = "INSERT INTO unidadejecutora (idUnidadEjecutora, nomUnidadEjecutora, desUnidadEjecutora) 
            VALUES (:id, :nombre, :desc)";
        $stmt = $this->db->prepare($sql);

        $resultado = $stmt->execute([
            'id' => $nuevoId,
            'nombre' => trim($nombre),
            'desc' => trim($descripcion)
        ]);

        if ($resultado) {
            return $nuevoId;
        } else {
            return false;
        }
    }

    public function eliminarUnidadE($id)
    {
        try {
            $sql = "DELETE FROM unidadejecutora WHERE idUnidadEjecutora = ?";
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
        $sql = "SELECT * FROM unidadejecutora WHERE idUnidadEjecutora = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarUnidadE($id, $nombre, $desc)
    {
        $sql = "UPDATE unidadejecutora SET nomUnidadEjecutora = :nombre, desUnidadEjecutora = :desc WHERE idUnidadEjecutora = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nombre' => trim($nombre),
            'desc' => trim($desc)
        ]);
    }
}