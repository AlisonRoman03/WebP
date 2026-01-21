/*======== Calendar Init (MENÚ DIARIO) =========*/
/*============================================*/

var calendar;

document.addEventListener("DOMContentLoaded", function () {

    const calendarEl = document.getElementById("calendar");

    calendar = new FullCalendar.Calendar(calendarEl, {
        locale: "es",
        initialView: "dayGridMonth",
        initialDate: new Date(),
        height: "auto",

        headerToolbar: {
            left: "prev,next today",
            center: "title",
            right: "dayGridMonth,timeGridWeek,timeGridDay"
        },

        events: function (info, successCallback, failureCallback) {

            fetch("../../../php/menu/menu_listado.php")
                .then(res => res.json())
                .then(data => {
                    successCallback(data);
                })
                .catch(err => {
                    console.error("Error cargando eventos", err);
                    failureCallback(err);
                });
        },

        eventClick: function (info) {
            const evento = info.event;

            // Cargar datos en el modal de edición
            document.getElementById("txtdescripcion").value = evento.title;
            document.getElementById("txtprecio").value = evento.extendedProps.precio;
            document.getElementById("txtunidad").value = evento.extendedProps.unidad;
            document.getElementById("dtfecha").value = evento.startStr.substring(0, 10);
            document.getElementById("txthora").value = evento.extendedProps.hora;

            document.getElementById("actmenu").dataset.id = evento.id;

            new bootstrap.Modal(document.getElementById("menuModal")).show();
        }
    });

    calendar.render();
});
