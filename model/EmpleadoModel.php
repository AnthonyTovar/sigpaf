<?php
require_once 'ConexionModel.php';

class EmpleadoModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function generarNuevoId()
    {
        $sql = "SELECT idEmpleado FROM empleado ORDER BY idEmpleado DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "EM0001";
        }

        $numero = substr($ultimoId, 2);
        $nuevoNumero = intval($numero) + 1;

        return "EM" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
    }

    // ============================================
    // NUEVO: Verificar si una cédula ya existe
    // ============================================
    public function existeCedula($cedula, $excluirId = null)
    {
        if ($excluirId) {
            $sql = "SELECT COUNT(*) FROM empleado WHERE cedulaEmpleado = :cedula AND idEmpleado != :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['cedula' => $cedula, 'id' => $excluirId]);
        } else {
            $sql = "SELECT COUNT(*) FROM empleado WHERE cedulaEmpleado = :cedula";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['cedula' => $cedula]);
        }
        return $stmt->fetchColumn() > 0;
    }

    public function listarEmpleados()
    {
        $sql = "SELECT e.*, c.nombreCargo, u.nomUnidadEjecutora 
                FROM empleado e 
                LEFT JOIN cargo c ON e.idCargo = c.idCargo 
                LEFT JOIN unidadEjecutora u ON e.idUnidadEjecutora = u.idUnidadEjecutora 
                ORDER BY e.idEmpleado ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarCargos()
    {
        $sql = "SELECT idCargo, nombreCargo FROM cargo ORDER BY nombreCargo ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarUnidadesEjecutoras()
    {
        $sql = "SELECT idUnidadEjecutora, nomUnidadEjecutora FROM unidadEjecutora ORDER BY nomUnidadEjecutora ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ============================================
    // CORREGIDO: Convierte V/E a "Venezolano"/"Extranjero"
    // y valida cédula duplicada antes de insertar
    // ============================================
    public function registrarEmpleado($prefijoNacionalidad, $cedula, $nombres, $apellidos, $fechaNac, $telefono, $correo, $idCargo, $idUnidadEjecutora)
    {
        // Convertir prefijo a texto completo para la BD
        $nacionalidad = ($prefijoNacionalidad === 'E') ? 'Extranjero' : 'Venezolano';

        // Verificar si la cédula ya existe
        if ($this->existeCedula($cedula)) {
            return 'cedula_duplicada';
        }

        $nuevoId = $this->generarNuevoId();

        $sql = "INSERT INTO empleado (idEmpleado, nacionalidad, cedulaEmpleado, nombres, apellidos, fechaNacimiento, telefonoEmpleado, correoEmpleado, idCargo, idUnidadEjecutora) 
                VALUES (:id, :nacionalidad, :cedula, :nombres, :apellidos, :fechaNac, :telefono, :correo, :idCargo, :idUnidadEjecutora)";
        $stmt = $this->db->prepare($sql);

        $resultado = $stmt->execute([
            'id' => $nuevoId,
            'nacionalidad' => $nacionalidad,
            'cedula' => $cedula,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'fechaNac' => $fechaNac,
            'telefono' => $telefono,
            'correo' => $correo,
            'idCargo' => $idCargo,
            'idUnidadEjecutora' => $idUnidadEjecutora
        ]);

        return $resultado ? $nuevoId : false;
    }

    public function eliminarEmpleado($id)
    {
        try {
            $sql = "DELETE FROM empleado WHERE idEmpleado = ?";
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

    public function obtenerEmpleadoPorId($id)
    {
        $sql = "SELECT e.*, c.nombreCargo, u.nomUnidadEjecutora 
                FROM empleado e 
                LEFT JOIN cargo c ON e.idCargo = c.idCargo 
                LEFT JOIN unidadEjecutora u ON e.idUnidadEjecutora = u.idUnidadEjecutora 
                WHERE e.idEmpleado = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ============================================
    // CORREGIDO: Valida cédula duplicada al editar
    // (excluyendo el propio registro)
    // ============================================
    public function actualizarEmpleado($id, $prefijoNacionalidad, $cedula, $nombres, $apellidos, $fechaNac, $telefono, $correo, $idCargo, $idUnidadEjecutora)
    {
        // Convertir prefijo a texto completo para la BD
        $nacionalidad = ($prefijoNacionalidad === 'E') ? 'Extranjero' : 'Venezolano';

        // Verificar si la cédula ya existe en OTRO empleado
        if ($this->existeCedula($cedula, $id)) {
            return 'cedula_duplicada';
        }

        $sql = "UPDATE empleado 
                SET nacionalidad = :nacionalidad, cedulaEmpleado = :cedula, nombres = :nombres, apellidos = :apellidos, 
                    fechaNacimiento = :fechaNac, telefonoEmpleado = :telefono, correoEmpleado = :correo,
                    idCargo = :idCargo, idUnidadEjecutora = :idUnidadEjecutora 
                WHERE idEmpleado = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nacionalidad' => $nacionalidad,
            'cedula' => $cedula,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'fechaNac' => $fechaNac,
            'telefono' => $telefono,
            'correo' => $correo,
            'idCargo' => $idCargo,
            'idUnidadEjecutora' => $idUnidadEjecutora
        ]);
    }
}