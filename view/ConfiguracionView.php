<div class="config-page">

    <div class="config-header">
        <h2><i class="bi bi-gear"></i> Configuración del Sistema</h2>
        <p>Gestiona todos los módulos y parámetros del sistema SIGPAF</p>
    </div>

    <?php if (isset($_GET['bd'])): ?>
        <?php if ($_GET['bd'] === 'ok'): ?>
            <div class="alert alert-success shadow-sm d-flex align-items-start" role="alert">
                <i class="bi bi-check-circle-fill me-2 mt-1"></i>
                <div>
                    <strong>Base de datos restaurada correctamente.</strong>
                    <div class="small"><?php echo htmlspecialchars($_GET['detalle'] ?? ''); ?></div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger shadow-sm d-flex align-items-start" role="alert">
                <i class="bi bi-x-circle-fill me-2 mt-1"></i>
                <div>
                    <strong>No se pudo restaurar la base de datos.</strong>
                    <div class="small"><?php echo htmlspecialchars($_GET['detalle'] ?? ''); ?></div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="config-grid">
        <a href="index.php?action=cargos" class="config-item">
            <i class="bi bi-person-badge"></i>
            <span>Cargos</span>
        </a>
        <a href="index.php?action=unidad" class="config-item">
            <i class="bi bi-building"></i>
            <span>Unidad Ejecutora</span>
        </a>
        <a href="index.php?action=areasEspecificas" class="config-item">
            <i class="bi bi-grid-3x3-gap"></i>
            <span>Áreas Específicas</span>
        </a>
        <a href="index.php?action=listarVertice" class="config-item">
            <i class="bi bi-diagram-3"></i>
            <span>Vértices</span>
        </a>
        <a href="index.php?action=listarGrupoEtario" class="config-item">
            <i class="bi bi-people-fill"></i>
            <span>Grupo Etario</span>
        </a>
        <a href="index.php?action=gruposEtnios" class="config-item">
            <i class="bi bi-globe-americas"></i>
            <span>Grupos Étnicos</span>
        </a>
        <a href="index.php?action=estados" class="config-item">
            <i class="bi bi-geo-alt"></i>
            <span>Estados</span>
        </a>
        <a href="index.php?action=municipios" class="config-item">
            <i class="bi bi-geo"></i>
            <span>Municipios</span>
        </a>
        <a href="index.php?action=parroquias" class="config-item">
            <i class="bi bi-geo-alt-fill"></i>
            <span>Parroquias</span>
        </a>
        <a href="index.php?action=horarios" class="config-item">
            <i class="bi bi-clock"></i>
            <span>Horarios</span>
        </a>
        <a href="index.php?action=estatus" class="config-item">
            <i class="bi bi-activity"></i>
            <span>Estatus</span>
        </a>
        <a href="index.php?action=tiposActividad" class="config-item">
            <i class="bi bi-list-task"></i>
            <span>Tipos de Actividad</span>
        </a>
        <a href="index.php?action=lugaresActividad" class="config-item">
            <i class="bi bi-geo-alt-fill"></i>
            <span>Lugares de Actividad</span>
        </a>
        <a href="index.php?action=espaciosUtilizar" class="config-item">
            <i class="bi bi-building"></i>
            <span>Espacios a Utilizar</span>
        </a>
        <a href="index.php?action=empleados" class="config-item">
            <i class="bi bi-person-vcard"></i>
            <span>Empleados</span>
        </a>
        <a href="index.php?action=docentes" class="config-item">
            <i class="bi bi-person-workspace"></i>
            <span>Docentes</span>
        </a>
        <a href="index.php?action=tiposUsuario" class="config-item">
            <i class="bi bi-shield-lock"></i>
            <span>Tipos de Usuario</span>
        </a>
        <a href="index.php?action=usuarios" class="config-item">
            <i class="bi bi-person-lock"></i>
            <span>Usuarios</span>
        </a>
        <a href="index.php?action=tiposEntrega" class="config-item">
            <i class="bi bi-box-seam"></i>
            <span>Tipos de Entrega</span>
        </a>
        <a href="index.php?action=estrategiasDesarrollo" class="config-item">
            <i class="bi bi-lightbulb"></i>
            <span>Estrategias de Desarrollo</span>
        </a>
        <a href="index.php?action=unidadesMedida" class="config-item">
            <i class="bi bi-rulers"></i>
            <span>Unidades de Medida</span>
        </a>
    </div>

    <!-- ===== RESPALDO Y RESTAURACIÓN DE LA BASE DE DATOS (barra compacta) ===== -->
    <div class="config-respaldo">
        <div class="respaldo-box">
            <div class="respaldo-info">
                <i class="bi bi-download text-primary"></i>
                <div class="respaldo-txt">
                    <strong>Exportar base de datos</strong>
                    <small>.sql completo y portable, sin comentarios</small>
                </div>
            </div>
            <a href="index.php?action=exportarBd" class="btn btn-sm btn-primary">
                <i class="bi bi-filetype-sql me-1"></i> Descargar
            </a>
        </div>
        <div class="respaldo-box">
            <div class="respaldo-info">
                <i class="bi bi-upload text-danger"></i>
                <div class="respaldo-txt">
                    <strong>Importar respaldo</strong>
                    <small>Restaura un .sql — reemplaza los datos actuales</small>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                data-bs-target="#modalImportarBd">
                <i class="bi bi-database-up me-1"></i> Restaurar...
            </button>
        </div>
    </div>

</div>

<!-- ===== MODAL: IMPORTAR RESPALDO ===== -->
<div class="modal fade" id="modalImportarBd" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="index.php?action=importarBd" method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>Restaurar base de datos</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-danger small mb-3">
                        <i class="bi bi-exclamation-octagon-fill me-1"></i>
                        <strong>Atención:</strong> esta operación <strong>reemplaza por completo</strong> los datos
                        actuales del sistema con los del archivo. Antes de ejecutar, el sistema guardará
                        automáticamente un respaldo del estado actual en la carpeta <code>respaldos/</code>.
                    </div>

                    <label class="form-label fw-semibold text-secondary">Archivo de respaldo (.sql) <span class="text-danger">*</span></label>
                    <input type="file" name="archivoSql" class="form-control" accept=".sql" required>
                    <div class="form-text text-muted">Tamaño máximo: 32 MB.</div>

                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="confirmarImporte" value="1" id="checkConfirmarImporte">
                        <label class="form-check-label small" for="checkConfirmarImporte">
                            Entiendo que <strong>se reemplazarán todos los datos actuales</strong> de la base de datos.
                        </label>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger px-4" id="btnImportarBd" disabled>
                        <i class="bi bi-database-up me-1"></i> Sí, restaurar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// La casilla de confirmación habilita el botón de restaurar
$(document).ready(function () {
    $('#checkConfirmarImporte').on('change', function () {
        $('#btnImportarBd').prop('disabled', !this.checked);
    });
    // Al reabrir el modal: dejar todo en estado seguro
    $('#modalImportarBd').on('hidden.bs.modal', function () {
        $('#checkConfirmarImporte').prop('checked', false);
        $('#btnImportarBd').prop('disabled', true);
    });
});
</script>