<!-- Formulario Superior -->
<div class="card mb-4" style="border: 2px solid #007bff;">
    <div class="card-header bg-primary text-white text-center">
        <h5 class="mb-0">¡Bienvenido a la Aplicación para el registro, modificación y eliminación de tipos!</h5>
        <h4 class="mb-0 text-uppercase">GESTIÓN DE TIPOS DE DOTACIÓN</h4>
    </div>
    <div class="card-body">
        <form id="FormTipo">
            <input type="hidden" name="tipo_id" id="tipo_id">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">INGRESE EL NOMBRE</label>
                    <input type="text" name="tipo_nombre" id="tipo_nombre" class="form-control" 
                           placeholder="Ingrese aquí el nombre" required maxlength="50">
                </div>
                
                <div class="col-md-5 mb-3">
                    <label class="form-label">INGRESE LA DESCRIPCIÓN</label>
                    <textarea name="tipo_descripcion" id="tipo_descripcion" class="form-control" 
                              placeholder="Ingrese aquí la descripción" rows="1" maxlength="100"></textarea>
                </div>
                
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-success me-2" id="btnGuardar">
                        <i class="bi bi-save me-1"></i>Guardar
                    </button>
                    <button type="button" class="btn btn-secondary" id="btnLimpiar">
                        <i class="bi bi-arrow-clockwise me-1"></i>Limpiar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Tipos -->
<div class="card" style="border: 2px solid #28a745;">
    <div class="card-header bg-success text-white text-center">
        <h5 class="mb-0 text-uppercase">TIPOS REGISTRADOS EN LA BASE DE DATOS</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="TablaTipos">
                <thead class="table-dark">
                    <tr>
                        <th>No.</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        
        <!-- Paginación Manual -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                <span id="infoRegistros">Mostrando 1 a 10 de 0 registros</span>
            </div>
            <nav>
                <ul class="pagination mb-0">
                    <li class="page-item" id="btnAnterior">
                        <a class="page-link" href="#">Anterior</a>
                    </li>
                    <li class="page-item active">
                        <a class="page-link" href="#" id="numeroPagina">1</a>
                    </li>
                    <li class="page-item" id="btnSiguiente">
                        <a class="page-link" href="#">Siguiente</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= asset('build/js/tipos/index.js'); ?>"></script>