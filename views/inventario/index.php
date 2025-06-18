<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-boxes me-2"></i>Inventario de Dotaciones</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ModalInventario">
        <i class="bi bi-plus-circle me-2"></i>Agregar Inventario
    </button>
</div>

<div class="card shadow">
    <div class="card-body">
        <table class="table table-striped table-hover" id="TablaInventario">
            <thead class="table-dark">
                <tr>
                    <th>Tipo</th>
                    <th>Talla</th>
                    <th>Cantidad</th>
                    <th>Fecha Ingreso</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <!-- Se carga dinámicamente -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Agregar Inventario -->
<div class="modal fade" id="ModalInventario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Inventario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="FormInventario">
                    <div class="mb-3">
                        <label class="form-label">Tipo de Dotación</label>
                        <select name="tipo_id" class="form-select" required>
                            <option value="">Seleccionar...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Talla</label>
                        <select name="talla_id" class="form-select" required>
                            <option value="">Seleccionar...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cantidad</label>
                        <input type="number" name="cantidad" class="form-control" min="1" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="FormInventario" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('build/js/dotacion/inventario.js'); ?>"></script>
