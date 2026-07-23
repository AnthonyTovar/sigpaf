<?php
require_once 'ConexionModel.php';

class UsuarioModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // ============================================
    // MÉTODOS DE AUTENTICACIÓN
    // ============================================

    public function validarUsuario($username, $password)
    {
        $sql = "SELECT * FROM usuarios WHERE nombreUsuario = :user";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user' => $username]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            if (password_verify($password, $usuario['contrasena'])) {
                return $usuario;
            }
            if ($password === $usuario['contrasena']) {
                return $usuario;
            }
        }
        return false;
    }

    public function registrar($username, $password)
    {
        if (empty($username) || empty($password)) {
            return "Todos los campos son obligatorios.";
        }

        // Verificar si ya existe
        $sql = "SELECT COUNT(*) FROM usuarios WHERE nombreUsuario = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            return "El nombre de usuario ya existe.";
        }

        $nuevoId = $this->generarNuevoId();
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO usuarios (idUsuario, nombreUsuario, contrasena) VALUES (:id, :user, :pass)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $nuevoId,
            'user' => $username,
            'pass' => $hash
        ]);
    }

    // ============================================
    // MÉTODOS DEL MAESTRO CRUD
    // ============================================

    private function generarNuevoId()
    {
        $sql = "SELECT idUsuario FROM usuarios ORDER BY idUsuario DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "US0001";
        }

        $numero = substr($ultimoId, 2);
        $nuevoNumero = intval($numero) + 1;

        return "US" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
    }

    public function listarUsuarios()
    {
        $sql = "SELECT u.*, tu.rolUsuario, e.cedulaEmpleado, e.nombres, e.apellidos 
                FROM usuarios u 
                LEFT JOIN tipoUsuario tu ON u.idTipoUsuario = tu.idTipoUsuario 
                LEFT JOIN empleado e ON u.idEmpleado = e.idEmpleado 
                ORDER BY u.idUsuario ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarTiposUsuario()
    {
        $sql = "SELECT idTipoUsuario, rolUsuario FROM tipoUsuario ORDER BY rolUsuario ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarEmpleadosSinUsuario()
    {
        $sql = "SELECT e.idEmpleado, e.cedulaEmpleado, e.nombres, e.apellidos 
                FROM empleado e 
                WHERE e.idEmpleado NOT IN (SELECT idEmpleado FROM usuarios WHERE idEmpleado IS NOT NULL)
                ORDER BY e.nombres ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ============================================
    // NUEVO: BUSCAR EMPLEADO POR CÉDULA
    // ============================================
    public function buscarEmpleadoPorCedula($cedula)
    {
        $sql = "SELECT e.idEmpleado, e.cedulaEmpleado, e.nombres, e.apellidos 
                FROM empleado e 
                WHERE e.cedulaEmpleado = :cedula";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cedula' => $cedula]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function empleadoTieneUsuario($idEmpleado)
    {
        $sql = "SELECT u.idUsuario FROM usuarios u WHERE u.idEmpleado = :idEmpleado";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['idEmpleado' => $idEmpleado]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ? $resultado['idUsuario'] : false;
    }

    public function registrarUsuarioMaestro($nombreUsuario, $contrasena, $idTipoUsuario, $idEmpleado)
    {
        $nuevoId = $this->generarNuevoId();
        $hash = password_hash($contrasena, PASSWORD_BCRYPT);

        $sql = "INSERT INTO usuarios (idUsuario, nombreUsuario, contrasena, idTipoUsuario, idEmpleado) 
                VALUES (:id, :nombre, :contrasena, :idTipo, :idEmpleado)";
        $stmt = $this->db->prepare($sql);

        $resultado = $stmt->execute([
            'id' => $nuevoId,
            'nombre' => $nombreUsuario,
            'contrasena' => $hash,
            'idTipo' => $idTipoUsuario,
            'idEmpleado' => $idEmpleado
        ]);

        return $resultado ? $nuevoId : false;
    }

    public function eliminarUsuario($id)
    {
        try {
            $sql = "DELETE FROM usuarios WHERE idUsuario = ?";
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

    public function obtenerUsuarioPorId($id)
    {
        $sql = "SELECT u.*, tu.rolUsuario, e.cedulaEmpleado, e.nombres, e.apellidos 
                FROM usuarios u 
                LEFT JOIN tipoUsuario tu ON u.idTipoUsuario = tu.idTipoUsuario 
                LEFT JOIN empleado e ON u.idEmpleado = e.idEmpleado 
                WHERE u.idUsuario = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarUsuario($id, $nombreUsuario, $contrasena, $idTipoUsuario, $idEmpleado)
    {
        if (!empty($contrasena)) {
            $hash = password_hash($contrasena, PASSWORD_BCRYPT);
            $sql = "UPDATE usuarios 
                    SET nombreUsuario = :nombre, contrasena = :contrasena, 
                        idTipoUsuario = :idTipo, idEmpleado = :idEmpleado 
                    WHERE idUsuario = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'id' => $id,
                'nombre' => $nombreUsuario,
                'contrasena' => $hash,
                'idTipo' => $idTipoUsuario,
                'idEmpleado' => $idEmpleado
            ]);
        } else {
            $sql = "UPDATE usuarios 
                    SET nombreUsuario = :nombre, idTipoUsuario = :idTipo, idEmpleado = :idEmpleado 
                    WHERE idUsuario = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'id' => $id,
                'nombre' => $nombreUsuario,
                'idTipo' => $idTipoUsuario,
                'idEmpleado' => $idEmpleado
            ]);
        }
    }

    public function verificarNombreUsuario($nombreUsuario, $excluirId = null)
    {
        if ($excluirId) {
            $sql = "SELECT COUNT(*) FROM usuarios WHERE nombreUsuario = ? AND idUsuario != ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nombreUsuario, $excluirId]);
        } else {
            $sql = "SELECT COUNT(*) FROM usuarios WHERE nombreUsuario = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nombreUsuario]);
        }
        return $stmt->fetchColumn() > 0;
    }

    // ============================================
    // GESTIÓN DE PERFIL PROPIO
    // ============================================

    public function actualizarPerfil($id, $nombreUsuario, $contrasena)
    {
        if (!empty($contrasena)) {
            $hash = password_hash($contrasena, PASSWORD_BCRYPT);
            $sql = "UPDATE usuarios 
                    SET nombreUsuario = :nombre, contrasena = :contrasena 
                    WHERE idUsuario = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'id' => $id,
                'nombre' => $nombreUsuario,
                'contrasena' => $hash
            ]);
        } else {
            $sql = "UPDATE usuarios 
                    SET nombreUsuario = :nombre 
                    WHERE idUsuario = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'id' => $id,
                'nombre' => $nombreUsuario
            ]);
        }
    }
}