<?php
require_once 'ConexionModel.php';

class MunicipioModel
{
    private $db;

    public function __construct()
    {
        // Usamos la conexión centralizada Database::getConnection()
        $this->db = Database::getConnection();
    }

    /**
     * Genera la secuencia alfanumérica MUN0001, MUN0002...
     */
    private function generarNuevoId()
    {
        $sql = "SELECT idMunicipio FROM municipio WHERE idMunicipio LIKE 'MUN%' ORDER BY idMunicipio DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "MUN0001";
        }

        $numero = (int)substr($ultimoId, 3);
        $nuevoNumero = $numero + 1;

        return "MUN" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
    }

    // Listado con JOIN para mostrar el nombre del Estado en la tabla
    public function listarMunicipios()
    {
        $sql = "SELECT m.idMunicipio, m.nombreMunicipio, m.idEstado, e.nombreEstado 
                FROM municipio m
                INNER JOIN estado e ON m.idEstado = e.idEstado
                ORDER BY m.idMunicipio ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Método para llenar el select de Estados en el formulario
    public function listarEstados()
    {
        $sql = "SELECT idEstado, nombreEstado FROM estado ORDER BY nombreEstado ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // NUEVO: Obtener nombre del estado por ID (para respuesta AJAX)
    public function obtenerNombreEstado($idEstado)
    {
        $sql = "SELECT nombreEstado FROM estado WHERE idEstado = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $idEstado]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ? $resultado['nombreEstado'] : 'Desconocido';
    }

    public function registrarMunicipio($nombre, $idEstado)
    {
        try {
            $nuevoId = $this->generarNuevoId();

            $sql = "INSERT INTO municipio (idMunicipio, nombreMunicipio, idEstado) 
                    VALUES (:id, :nombre, :idEst)";
            $stmt = $this->db->prepare($sql);

            $resultado = $stmt->execute([
                'id'     => $nuevoId,
                'nombre' => $nombre,
                'idEst'  => $idEstado
            ]);

            return $resultado ? $nuevoId : false;
        } catch (PDOException $e) {
            // Log del error si fuera necesario
            return false;
        }
    }

    public function obtenerMunicipioPorId($id)
    {
        $sql = "SELECT * FROM municipio WHERE idMunicipio = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarMunicipio($id, $nombre, $idEstado)
    {
        try {
            $sql = "UPDATE municipio 
                    SET nombreMunicipio = :nombre, idEstado = :idEst 
                    WHERE idMunicipio = :id";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                'id'     => $id,
                'nombre' => $nombre,
                'idEst'  => $idEstado
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function eliminarMunicipio($id)
    {
        try {
            $sql = "DELETE FROM municipio WHERE idMunicipio = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);

            return ($stmt->rowCount() > 0);
        } catch (PDOException $e) {
            // Error de integridad referencial (código 23000)
            // Ocurre si el municipio está siendo usado en otra tabla (ej. Parroquias)
            if ($e->getCode() == '23000') {
                return "link";
            }
            return false;
        }
    }
}