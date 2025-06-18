import Swal from "sweetalert2";

let tablaInventario;

document.addEventListener('DOMContentLoaded', function() {
    cargarInventario();
    cargarTipos();
    cargarTallas();
    configurarFormulario();
});

async function cargarInventario() {
    try {
        const respuesta = await fetch('/dotacion/obtenerInventarioAPI');
        const inventarios = await respuesta.json();
        
        if(tablaInventario) {
            tablaInventario.destroy();
        }
        
        mostrarInventario(inventarios);
        inicializarDataTable();
    } catch (error) {
        Swal.fire('Error', 'No se pudo cargar el inventario', 'error');
    }
}

function mostrarInventario(inventarios) {
    const tbody = document.querySelector('#TablaInventario tbody');
    tbody.innerHTML = '';
    
    inventarios.forEach(item => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${item.tipo_nombre}</td>
            <td>${item.talla_etiqueta}</td>
            <td><span class="badge bg-primary">${item.cantidad}</span></td>
            <td>${item.fecha_ingreso}</td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="editarInventario(${item.inv_id})">
                    <i class="bi bi-pencil"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

async function cargarTipos() {
    try {
        const respuesta = await fetch('/tipos/obtenerAPI');
        const tipos = await respuesta.json();
        
        const select = document.querySelector('select[name="tipo_id"]');
        if(!select) return;
        
        select.innerHTML = '<option value="">Seleccionar...</option>';
        
        tipos.forEach(tipo => {
            const option = document.createElement('option');
            option.value = tipo.tipo_id;
            option.textContent = tipo.tipo_nombre;
            select.appendChild(option);
        });
    } catch (error) {
        console.error('Error al cargar tipos:', error);
    }
}

async function cargarTallas() {
    try {
        const respuesta = await fetch('/tallas/obtenerAPI');
        const tallas = await respuesta.json();
        
        const select = document.querySelector('select[name="talla_id"]');
        if(!select) return;
        
        select.innerHTML = '<option value="">Seleccionar...</option>';
        
        tallas.forEach(talla => {
            const option = document.createElement('option');
            option.value = talla.talla_id;
            option.textContent = talla.talla_etiqueta;
            select.appendChild(option);
        });
    } catch (error) {
        console.error('Error al cargar tallas:', error);
    }
}

function configurarFormulario() {
    const formulario = document.getElementById('FormInventario');
    
    if(formulario) {
        formulario.addEventListener('submit', function(e) {
            e.preventDefault();
            guardarInventario();
        });
    }
}

async function guardarInventario() {
    try {
        Swal.fire({
            title: 'Guardando...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        const datos = new FormData(document.getElementById('FormInventario'));
        
        const respuesta = await fetch('/dotacion/guardarInventarioAPI', {
            method: 'POST',
            body: datos
        });
        
        const resultado = await respuesta.json();
        
        if(resultado.resultado) {
            Swal.fire('Éxito', resultado.mensaje, 'success');
            document.getElementById('FormInventario').reset();
            bootstrap.Modal.getInstance(document.getElementById('ModalInventario')).hide();
            cargarInventario();
        } else {
            Swal.fire('Error', resultado.mensaje, 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'Error al guardar inventario', 'error');
    }
}

function inicializarDataTable() {
    tablaInventario = $('#TablaInventario').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        responsive: true,
        pageLength: 25
    });
}

window.editarInventario = function(id) {
    // Función para editar inventario
    console.log('Editar inventario:', id);
};