<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people me-2"></i>Gestión de Usuarios</h2>
    <a href="/usuarios/crear" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Nuevo Usuario
    </a>
</div>

<div class="card shadow">
    <div class="card-body">
        <table class="table table-striped table-hover" id="TablaUsuarios">
            <thead class="table-dark">
                <tr>
                    <th>Foto</th>
                    <th>Nombre</th>
                    <th>Código</th>
                    <th>Correo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <!-- Se carga dinámicamente con JS -->
            </tbody>
        </table>
    </div>
</div>

<script src="<?= asset('build/js/usuarios/index.js'); ?>"></script>