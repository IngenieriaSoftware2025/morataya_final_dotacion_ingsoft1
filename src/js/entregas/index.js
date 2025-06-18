import Swal from "sweetalert2";

let tablaPendientes, tablaEntregas;

document.addEventListener('DOMContentLoaded', function() {
    cargarDatos();
});

async function cargarDatos() {
    await cargarSolicitudesPendientes();
    await cargarEntregasRealizadas();
}

async function cargarSolicitudesPendientes() {
    try {
        const respuesta = await fetch('/dotacion/obtenerSolicitudesPendientesAPI');
        const pendientes = await respuesta.json();
        
        if(tablaPendientes) {
            tablaPendientes.destroy();
        }
        
        mostrarPendientes(pendientes);
        inicializarTablaPendientes();
    } catch (error) {
        console.error('Error al cargar pendientes:', error);
    }
}

async function cargarEntregasRealizadas() {
    try {
        const respuesta = await fetch('/dotacion/obtenerEntregasAPI');
        const entregas = await respuesta.json();
        
        if(tablaEntregas) {
            tablaEntregas.destroy();
        }
        
        mostrarEntregas(entregas);
        inicializarTablaEntregas();
    } catch (error) {
        console.error('Error al cargar entregas:', error);
    }
}

function mostrarPendientes(pendientes) {
    const tbody = document.querySelector('#TablaPendientes tbody');
    tbody.innerHTML = '';
    
    pendientes.forEach(solicitud => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${solicitud.personal_nombre}</td>
            <td>${solicitud.tipo_nombre} / ${solicitud.talla_etiqueta}</td>
            <td>
                <button class="btn btn-sm btn-success" onclick="entregarSolicitud(${solicitud.solicitud_id})">
                    <i class="bi bi-check"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function mostrarEntregas(entregas) {
    const tbody = document.querySelector('#TablaEntregas tbody');
    tbody.innerHTML = '';
    
    entregas.forEach(entrega => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${entrega.personal_nombre}</td>
            <td>${entrega.tipo_nombre} / ${entrega.talla_etiqueta}</td>
            <td>${entrega.fecha_entrega}</td>
        `;
        tbody.appendChild(tr);
    });
}

window.entregarSolicitud = async function(solicitudId) {
    try {
        const confirmacion = await Swal.fire({
            title: '¿Confirmar entrega?',
            text: "Esta acción no se puede deshacer",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, entregar',
            cancelButtonText: 'Cancelar'
        });
        
        if(!confirmacion.isConfirmed) return;
        
        Swal.fire({
            title: 'Procesando entrega...',
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
            cargarDatos();
        } else {
            Swal.fire('Error', resultado.mensaje, 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'Error al procesar entrega', 'error');
    }
};

function inicializarTablaPendientes() {
    tablaPendientes = $('#TablaPendientes').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        responsive: true,
        pageLength: 10,
        searching: false,
        info: false
    });
}

function inicializarTablaEntregas() {
    tablaEntregas = $('#TablaEntregas').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        responsive: true,
        pageLength: 10,
        order: [[2, 'desc']]
    });
}