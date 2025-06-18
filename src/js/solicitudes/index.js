import Swal from "sweetalert2";

let tablaSolicitudes;

document.addEventListener('DOMContentLoaded', function() {
    cargarSolicitudes();
    cargarPersonal();
    cargarTipos();
    cargarTallas();
    configurarFormulario();
});

async function cargarSolicitudes() {
    try {
        const respuesta = await fetch('/dotacion/obtenerSolicitudesAPI');
        const solicitudes = await respuesta.json();
        
        if(tablaSolicitudes) {
            tablaSolicitudes.destroy();
        }
        
        mostrarSolicitudes(solicitudes);
        inicializarDataTable();
    } catch (error) {
        Swal.fire('Error', 'No se pudieron cargar las solicitudes', 'error');
    }
}

function mostrarSolicitudes(solicitudes) {
    const tbody = document.querySelector('#TablaSolicitudes tbody');
    tbody.innerHTML = '';
    
    solicitudes.forEach(solicitud => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${solicitud.personal_nombre}</td>
            <td>${solicitud.tipo_nombre}</td>
            <td>${solicitud.talla_etiqueta}</td>
            <td>${solicitud.fecha_solicitud}</td>
            <td>
                <span class="badge bg-${solicitud.estado_entrega == 1 ? 'success' : 'warning'}">
                    ${solicitud.estado_entrega == 1 ? 'Entregado' : 'Pendiente'}
                </span>
            </td>
            <td>
                ${solicitud.estado_entrega == 0 ? 
                    `<button class="btn btn-sm btn-success" onclick="procesarEntrega(${solicitud.solicitud_id})">
                        <i class="bi bi-check"></i> Entregar
                    </button>` : '-'
                }
            </td>
        `;
        tbody.appendChild(tr);
    });
}

async function cargarPersonal() {
    try {
        const respuesta = await fetch('/personal/obtenerAPI');
        const personal = await respuesta.json();
        
        const select = document.querySelector('select[name="personal_id"]');
        if(!select) return;
        
        select.innerHTML = '<option value="">Seleccionar...</option>';
        
        personal.forEach(persona => {
            const option = document.createElement('option');
            option.value = persona.personal_id;
            option.textContent = persona.personal_nombre;
            select.appendChild(option);
        });
    } catch (error) {
        console.error('Error al cargar personal:', error);
    }
}

async function cargarTipos() {
    try {
        const respuesta = await fetch('/tipos/obtenerAPI');
        const tipos = await respuesta.json();
        
        const select = document.querySelector('#ModalSolicitud select[name="tipo_id"]');
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
        
        const select = document.querySelector('#ModalSolicitud select[name="talla_id"]');
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
    const formulario = document.getElementById('FormSolicitud');
    
    if(formulario) {
        formulario.addEventListener('submit', function(e) {
            e.preventDefault();
            guardarSolicitud();
        });
    }
}

async function guardarSolicitud() {
    try {
        Swal.fire({
            title: 'Guardando...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        const datos = new FormData(document.getElementById('FormSolicitud'));
        
        const respuesta = await fetch('/dotacion/guardarSolicitudAPI', {
            method: 'POST',
            body: datos
        });
        
        const resultado = await respuesta.json();
        
        if(resultado.resultado) {
            Swal.fire('Éxito', resultado.mensaje, 'success');
            document.getElementById('FormSolicitud').reset();
            bootstrap.Modal.getInstance(document.getElementById('ModalSolicitud')).hide();
            cargarSolicitudes();
        } else {
            Swal.fire('Error', resultado.mensaje, 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'Error al guardar solicitud', 'error');
    }
}

window.procesarEntrega = async function(solicitudId) {
    try {
        const confirmacion = await Swal.fire({
            title: '¿Procesar entrega?',
            text: "Esta acción marcará la solicitud como entregada",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, entregar',
            cancelButtonText: 'Cancelar'
        });
        
        if(!confirmacion.isConfirmed) return;
        
        Swal.fire({
            title: 'Procesando...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        const datos = new FormData();
        datos.append('solicitud_id', solicitudId);
        
        const respuesta = await fetch('/dotacion/procesarEntregaAPI', {
            method: 'POST',
            body: datos
        });
        
        const resultado = await respuesta.json();
        
        if(resultado.resultado) {
            Swal.fire('Éxito', resultado.mensaje, 'success');
            cargarSolicitudes();
        } else {
            Swal.fire('Error', resultado.mensaje, 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'Error al procesar entrega', 'error');
    }
};

function inicializarDataTable() {
    tablaSolicitudes = $('#TablaSolicitudes').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        responsive: true,
        pageLength: 25
    });
}
