<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="#">Catálogos</a></li>
        <li class="breadcrumb-item active">Tallas</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-rulers me-2"></i>Tallas</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ModalTalla">
        <i class="bi bi-plus-circle me-2"></i>Nueva Talla
    </button>
</div>

<div class="card shadow">
    <div class="card-body">
        <table class="table table-striped table-hover" id="TablaTallas">
            <thead class="table-dark">
                <tr>
                    <th>Etiqueta</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="ModalTalla" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Talla</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="FormTalla">
                    <div class="mb-3">
                        <label class="form-label">Etiqueta <span class="text-danger">*</span></label>
                        <input type="text" name="talla_etiqueta" class="form-control" required maxlength="10" 
                               placeholder="Ej: S, M, L, XL o 38, 40, 42">
                        <div class="form-text">
                            Formatos válidos: XS, S, M, L, XL, XXL o números de 35-50
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="FormTalla" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    cargarTallas();
    configurarFormulario();
});

let tablaTallas;

async function cargarTallas() {
    try {
        const respuesta = await fetch('/tallas/obtenerAPI');
        const tallas = await respuesta.json();
        
        if(tablaTallas) {
            tablaTallas.destroy();
        }
        
        mostrarTallas(tallas);
        inicializarDataTable();
    } catch (error) {
        console.error('Error al cargar tallas:', error);
    }
}

function mostrarTallas(tallas) {
    const tbody = document.querySelector('#TablaTallas tbody');
    tbody.innerHTML = '';
    
    tallas.forEach(talla => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><strong>${talla.talla_etiqueta}</strong></td>
            <td>
                <span class="badge bg-${talla.talla_situacion == 1 ? 'success' : 'danger'}">
                    ${talla.talla_situacion == 1 ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>
                <button class="btn btn-sm btn-outline-danger" onclick="eliminarTalla(${talla.talla_id})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function configurarFormulario() {
    const formulario = document.getElementById('FormTalla');
    
    if(formulario) {
        formulario.addEventListener('submit', function(e) {
            e.preventDefault();
            guardarTalla();
        });
    }
}

async function guardarTalla() {
    try {
        const datos = new FormData(document.getElementById('FormTalla'));
        
        const respuesta = await fetch('/tallas/guardarAPI', {
            method: 'POST',
            body: datos
        });
        
        const resultado = await respuesta.json();
        
        if(resultado.resultado) {
            alert('Talla guardada correctamente');
            document.getElementById('FormTalla').reset();
            bootstrap.Modal.getInstance(document.getElementById('ModalTalla')).hide();
            cargarTallas();
        } else {
            alert('Error: ' + resultado.mensaje);
        }
    } catch (error) {
        alert('Error al guardar talla');
    }
}

window.eliminarTalla = async function(id) {
    if(confirm('¿Estás seguro de eliminar esta talla?')) {
        try {
            const respuesta = await fetch(`/tallas/eliminarAPI?talla_id=${id}`);
            const resultado = await respuesta.json();
            
            if(resultado.resultado) {
                alert('Talla eliminada correctamente');
                cargarTallas();
            } else {
                alert('Error: ' + resultado.mensaje);
            }
        } catch (error) {
            alert('Error al eliminar talla');
        }
    }
};

function inicializarDataTable() {
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        tablaTallas = $('#TablaTallas').DataTable({
            responsive: true,
            pageLength: 25,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            }
        });
    }
}
</script>