import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es';

function toLocalInputValue(date) {
  // convierte Date -> YYYY-MM-DDTHH:mm (sin segundos)
  const pad = (n) => String(n).padStart(2, '0');
  const yyyy = date.getFullYear();
  const mm = pad(date.getMonth() + 1);
  const dd = pad(date.getDate());
  const hh = pad(date.getHours());
  const mi = pad(date.getMinutes());
  return `${yyyy}-${mm}-${dd}T${hh}:${mi}`;
}

function addMinutes(date, minutes) {
  return new Date(date.getTime() + minutes * 60000);
}

function setStartEndNowSuggestion(startAtEl, endAtEl) {
  // siguiente hora redondeada a 30 min (ej: 10:00 o 10:30)
  const now = new Date();
  const mins = now.getMinutes();
  const rounded = mins < 30 ? 30 : 60;
  now.setMinutes(rounded === 60 ? 0 : 30, 0, 0);
  if (rounded === 60) now.setHours(now.getHours() + 1);

  startAtEl.value = toLocalInputValue(now);

  // default 60 min (luego se ajusta por paquete/duración si aplica)
  const defaultEnd = addMinutes(now, 60);
  endAtEl.value = toLocalInputValue(defaultEnd);
}

document.addEventListener('DOMContentLoaded', () => {
  const calendarEl = document.getElementById('calendar');
  const dataEl = document.getElementById('agenda-data');

  if (!calendarEl || !dataEl) return; // solo corre en /agenda

  const csrf = dataEl.dataset.csrf;
  const eventsUrl = dataEl.dataset.eventsUrl;
  const packagesUrlTemplate = dataEl.dataset.patientPackagesUrl;

  const prefillPatientId = dataEl.dataset.prefillPatientId || '';
  const prefillPackageId = dataEl.dataset.prefillPackageId || '';

  const modal = document.getElementById('appointmentModal');
  const form = document.getElementById('appointmentForm');
  const modalTitle = document.getElementById('modalTitle');
  const modalSubtitle = document.getElementById('modalSubtitle');
  const modalError = document.getElementById('modalError');

  const appointmentId = document.getElementById('appointment_id');
  const patientId = document.getElementById('patient_id');
  const treatmentId = document.getElementById('treatment_id');
  const patientTreatmentId = document.getElementById('patient_treatment_id');

  const specialistId = document.getElementById('specialist_id');
  const status = document.getElementById('status');
  const startAt = document.getElementById('start_at');
  const endAt = document.getElementById('end_at');
  const notes = document.getElementById('notes');
const btnCancelAppointment = document.getElementById('btnCancelAppointment');
const btnNoShow = document.getElementById('btnNoShow');
const btnCompleteAppointment = document.getElementById('btnCompleteAppointment');
const btnOpenNewAppointment = document.getElementById('btnOpenNewAppointment');

  const openModal = () => {
    modal.classList.remove('hidden');
    modalError.classList.add('hidden');
    modalError.textContent = '';
  };

  const closeModal = () => {
    modal.classList.add('hidden');
  };

  // cerrar al click fuera / botones
  modal.addEventListener('click', (e) => {
    if (e.target?.dataset?.close === '1') closeModal();
  });
  document.querySelectorAll('[data-close="1"]').forEach((btn) => {
    btn.addEventListener('click', closeModal);
  });

  // =========================
  // Paquetes por paciente
  // =========================
  async function loadPatientPackages(patient_id, preselectId = null) {
    if (!patientTreatmentId) return;

    patientTreatmentId.innerHTML = `<option value="">Cargando...</option>`;

    if (!patient_id) {
      patientTreatmentId.innerHTML = `<option value="">— Selecciona paciente primero —</option>`;
      return;
    }

    const url = packagesUrlTemplate.replace('/0/', `/${patient_id}/`);

    try {
      const res = await fetch(url, { headers: { Accept: 'application/json' } });
      const items = await res.json();

      if (!Array.isArray(items) || items.length === 0) {
        patientTreatmentId.innerHTML = `<option value="">— Sin paquetes activos —</option>`;
        return;
      }

      patientTreatmentId.innerHTML = `<option value="">— Selecciona —</option>`;
      for (const it of items) {
        const opt = document.createElement('option');
        opt.value = it.id;
        opt.textContent = it.label;

        // para sincronizar tratamiento/duración desde paquete
        opt.dataset.treatmentId = it.treatment_id;
        opt.dataset.duration = it.duration_minutes;
        opt.dataset.color = it.color_hex;

        patientTreatmentId.appendChild(opt);
      }

      if (preselectId) {
        patientTreatmentId.value = String(preselectId);
        patientTreatmentId.dispatchEvent(new Event('change'));
      }
    } catch (e) {
      patientTreatmentId.innerHTML = `<option value="">Error al cargar paquetes</option>`;
    }
  }



function openNewAppointmentModal(startDate = null, endDate = null) {
  appointmentId.value = '';
  modalTitle.textContent = 'Nueva cita';
  modalSubtitle.textContent = 'Se guardará el nombre de quien agendó.';

  btnCancelAppointment?.classList.add('hidden');
  btnNoShow?.classList.add('hidden');
  btnCompleteAppointment?.classList.add('hidden');

  patientId.value = '';

  if (patientTreatmentId) {
    patientTreatmentId.innerHTML = `<option value="">— Selecciona paciente primero —</option>`;
    patientTreatmentId.value = '';
  }

  if (treatmentId) {
    treatmentId.value = '';
  }

  if (specialistId?.dataset?.currentUserId) {
    specialistId.value = specialistId.dataset.currentUserId;
  }

  status.value = 'confirmed';
  notes.value = '';

  if (startDate && endDate) {
    startAt.value = toLocalInputValue(startDate);
    endAt.value = toLocalInputValue(endDate);
  } else {
    setStartEndNowSuggestion(startAt, endAt);
  }

  openModal();
}



  // Cuando cambia paciente: cargar paquetes activos
  if (patientId && patientTreatmentId) {
    patientId.addEventListener('change', async () => {
      await loadPatientPackages(patientId.value);
    });

    // Cuando cambia paquete: sincroniza tratamiento y sugiere end_at según duración
    patientTreatmentId.addEventListener('change', () => {
      const opt = patientTreatmentId.options[patientTreatmentId.selectedIndex];
      const tId = opt?.dataset?.treatmentId;
      const duration = Number(opt?.dataset?.duration || 0);

      if (tId && treatmentId) {
        treatmentId.value = String(tId);
      }

      if (duration && startAt?.value && endAt) {
        const startDate = new Date(startAt.value);
        const suggestedEnd = addMinutes(startDate, duration);
        endAt.value = toLocalInputValue(suggestedEnd);
      }
    });
  }

  // Autollenar fin según duración del tratamiento (si usuario cambia manual)
  if (treatmentId) {
    treatmentId.addEventListener('change', () => {
      const opt = treatmentId.options[treatmentId.selectedIndex];
      const duration = Number(opt?.dataset?.duration || 0);
      if (!duration) return;

      if (startAt?.value && endAt) {
        const startDate = new Date(startAt.value);
        const suggestedEnd = addMinutes(startDate, duration);
        endAt.value = toLocalInputValue(suggestedEnd);
      }
    });
  }

  // =========================
  // FullCalendar
  // =========================
  const calendar = new Calendar(calendarEl, {
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    locales: [esLocale],
    locale: 'es',
    initialView: 'timeGridWeek',
    locale: 'es',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay',
    },
    selectable: true,
    editable: true,
    nowIndicator: true,
    slotMinTime: '07:00:00',
    slotMaxTime: '22:00:00',
    events: eventsUrl,

    // Bloquear crear en pasado
    selectAllow: (selectInfo) => {
      return selectInfo.start >= new Date();
    },

    // Bloquear mover/redimensionar hacia el pasado
    eventAllow: (dropInfo) => {
      return dropInfo.start >= new Date();
    },

select: async (info) => {
  if (info.start < new Date()) return;
  openNewAppointmentModal(info.start, info.end);
},

    eventClick: async (info) => {
      const ev = info.event;

      appointmentId.value = ev.id;

      modalTitle.textContent = 'Editar cita';
      modalSubtitle.textContent = ev.extendedProps?.creator_name
        ? `Agendada por: ${ev.extendedProps.creator_name}`
        : 'Editar información de la cita.';

      btnCancelAppointment.classList.remove('hidden');
      btnNoShow.classList.remove('hidden');
      patientId.value = ev.extendedProps?.patient_id ?? '';
      specialistId.value = ev.extendedProps?.specialist_id ?? '';
      status.value = ev.extendedProps?.status ?? 'confirmed';
      notes.value = ev.extendedProps?.notes ?? '';

      startAt.value = toLocalInputValue(ev.start);
      endAt.value = toLocalInputValue(ev.end || ev.start);

      // cargar paquetes y preseleccionar el que trae la cita
      if (patientTreatmentId) {
        await loadPatientPackages(
          patientId.value,
          ev.extendedProps?.patient_treatment_id ?? null
        );
      }

      // respaldo si no hay paquete
      treatmentId.value = ev.extendedProps?.treatment_id ?? '';

      openModal();
    },

    eventDrop: async (info) => {
      await quickUpdateFromCalendar(info);
    },

    eventResize: async (info) => {
      await quickUpdateFromCalendar(info);
    },
  });

  calendar.render();

  async function quickUpdateFromCalendar(info) {
    const ev = info.event;
    const id = ev.id;

    const payload = {
      patient_id: ev.extendedProps?.patient_id ?? null,
      patient_treatment_id: ev.extendedProps?.patient_treatment_id ?? null,
      treatment_id: ev.extendedProps?.treatment_id ?? null,
      specialist_id: ev.extendedProps?.specialist_id ?? null,
      status: ev.extendedProps?.status ?? 'confirmed',
      notes: ev.extendedProps?.notes ?? null,
      start_at: ev.start.toISOString(),
      end_at: (ev.end || ev.start).toISOString(),
    };

    try {
      const res = await fetch(`/api/agenda/appointments/${id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
          Accept: 'application/json',
        },
        body: JSON.stringify(payload),
      });

      if (!res.ok) {
        const data = await res.json().catch(() => ({}));
        alert(data?.message || 'No se pudo actualizar. Revisa empalmes.');
        info.revert();
        return;
      }

      calendar.refetchEvents();
    } catch (e) {
      info.revert();
      alert('Error de red al actualizar.');
    }
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const id = appointmentId.value;
    const isEdit = Boolean(id);

    const payload = {
      patient_id: patientId.value || null,
      patient_treatment_id: patientTreatmentId?.value || null,
      treatment_id: treatmentId.value || null,
      specialist_id: specialistId.value ? Number(specialistId.value) : null,
      status: status.value,
      notes: notes.value || null,
      start_at: new Date(startAt.value).toISOString(),
      end_at: new Date(endAt.value).toISOString(),
    };

    const url = isEdit ? `/api/agenda/appointments/${id}` : `/api/agenda/appointments`;
    const method = isEdit ? 'PUT' : 'POST';

    try {
      const res = await fetch(url, {
        method,
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
          Accept: 'application/json',
        },
        body: JSON.stringify(payload),
      });

      const data = await res.json().catch(() => ({}));

      if (!res.ok) {
        modalError.textContent = data?.message || 'Error al guardar. Verifica datos.';
        modalError.classList.remove('hidden');
        return;
      }

      closeModal();
      calendar.unselect();
      calendar.refetchEvents();
    } catch (err) {
      modalError.textContent = 'Error de red. Intenta nuevamente.';
      modalError.classList.remove('hidden');
    }
  });


if (btnOpenNewAppointment) {
  btnOpenNewAppointment.addEventListener('click', () => {
    openNewAppointmentModal();
  });
}

btnCancelAppointment.addEventListener('click', async () => {
  const id = appointmentId.value;
  if (!id) return;

  if (!confirm('¿Cancelar esta cita?')) return;

  try {
    const res = await fetch(`/api/agenda/appointments/${id}/cancel`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrf,
        Accept: 'application/json',
      },
    });

    const data = await res.json().catch(() => ({}));

    if (!res.ok) {
      modalError.textContent = data?.message || 'No se pudo cancelar la cita.';
      modalError.classList.remove('hidden');
      return;
    }

    closeModal();
    calendar.refetchEvents();
  } catch (e) {
    modalError.textContent = 'Error de red al cancelar.';
    modalError.classList.remove('hidden');
  }
});

btnNoShow.addEventListener('click', async () => {
  const id = appointmentId.value;
  if (!id) return;

  if (!confirm('¿Marcar esta cita como inasistencia (no-show)?')) return;

  try {
    const res = await fetch(`/api/agenda/appointments/${id}/no-show`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrf,
        Accept: 'application/json',
      },
    });

    const data = await res.json().catch(() => ({}));

    if (!res.ok) {
      modalError.textContent = data?.message || 'No se pudo marcar la inasistencia.';
      modalError.classList.remove('hidden');
      return;
    }

    closeModal();
    calendar.refetchEvents();
  } catch (e) {
    modalError.textContent = 'Error de red al marcar inasistencia.';
    modalError.classList.remove('hidden');
  }
});




btnCompleteAppointment.addEventListener('click', async () => {
  const id = appointmentId.value;
  if (!id) return;

  if (!confirm('¿Marcar esta cita como realizada?')) return;

  try {
    const res = await fetch(`/api/agenda/appointments/${id}/complete`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrf,
        Accept: 'application/json',
      },
    });

    const data = await res.json().catch(() => ({}));

    if (!res.ok) {
      modalError.textContent = data?.message || 'No se pudo marcar como realizada.';
      modalError.classList.remove('hidden');
      return;
    }

    closeModal();
    calendar.refetchEvents();
  } catch (e) {
    modalError.textContent = 'Error de red al marcar como realizada.';
    modalError.classList.remove('hidden');
  }
});


  // =========================
  // Prefill desde /pacientes (Agendar)
  // =========================
  async function prefillFromPatients() {
    if (!prefillPatientId) return;

    // configurar como NUEVA cita limpia
    appointmentId.value = '';
    modalTitle.textContent = 'Nueva cita';
    modalSubtitle.textContent = 'Se guardará el nombre de quien agendó.';
    btnDelete.classList.add('hidden');

    specialistId.value = '';
    status.value = 'confirmed';
    notes.value = '';

    // sugerir horario
    setStartEndNowSuggestion(startAt, endAt);

    // setear paciente y cargar paquetes
    patientId.value = String(prefillPatientId);
    await loadPatientPackages(patientId.value, prefillPackageId || null);

    // abrir modal
    openModal();
  }

  // corre una vez
  prefillFromPatients();
});
