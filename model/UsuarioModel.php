<?php
require_once 'ConexionModel.php';

class UsuarioModel
{
    private $db;

    /**
     * CONSTRUCTOR
     * Se ejecuta al instanciar la clase
     */
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * MÉTODO: generarNuevoId (Privado)
     * Este método se encarga de crear la secuencia alfanumérica Us001, Us002...
     */
    private function generarNuevoId()
    {
        $sql = "SELECT id FROM usuarios ORDER BY id DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "Us0001";
        }

        $numero = substr($ultimoId, 2);

        $nuevoNumero = intval($numero) + 1;

        return "Us" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
    }

    /**
     * MÉTODO: registrar
     * Se encarga de dar de alta a un nuevo usuario.
     * @param string
     * @param string
     * @return mixed
     */
    public function registrar($user, $password)
    {
        $stmtCheck = $this->db->prepare("SELECT id FROM usuarios WHERE username = :user");
        $stmtCheck->execute(['user' => $user]);

        if ($stmtCheck->rowCount() > 0) {
            return "El nombre de usuario ya está en uso.";
        }

        $nuevoId = $this->generarNuevoId();

        $passHash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->db->prepare("INSERT INTO usuarios (id, username, password) VALUES (:id, :user, :pass)");

        return $stmt->execute([
            'id' => $nuevoId,
            'user' => $user,
            'pass' => $passHash
        ]);
    }
    /**
     * MÉTODO: validarUsuario
     * Se utiliza en el Login para comprobar si las credenciales son correctas.
     * @param string
     * @param string
     * @return mixed
     */
    public function validarUsuario($user, $password)
    {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE nombreUsuario = :user");
        $stmt->execute(['user' => $user]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($password, $usuario['contrasena'])) {
            return $usuario;
        }

        return false;
    }
}