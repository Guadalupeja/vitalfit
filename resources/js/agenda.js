import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es';

function toLocalInputValue(date) {
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
  const now = new Date();
  const mins = now.getMinutes();
  const rounded = mins < 30 ? 30 : 60;

  now.setMinutes(rounded === 60 ? 0 : 30, 0, 0);
  if (rounded === 60) now.setHours(now.getHours() + 1);

  startAtEl.value = toLocalInputValue(now);
  endAtEl.value = toLocalInputValue(addMinutes(now, 60));
}

document.addEventListener('DOMContentLoaded', () => {
  const calendarEl = document.getElementById('calendar');
  const dataEl = document.getElementById('agenda-data');

  if (!calendarEl || !dataEl) return;

  const csrf = dataEl.dataset.csrf;
  const eventsUrl = dataEl.dataset.eventsUrl;
  const patientPackagesUrlTemplate = dataEl.dataset.patientPackagesUrl;
  const availableSpecialistsUrl = dataEl.dataset.availableSpecialistsUrl;
  const packageItemsUrlTemplate = dataEl.dataset.packageItemsUrl;
  const destroyUrlTemplate = dataEl.dataset.destroyUrlTemplate;

  const prefillPatientId = dataEl.dataset.prefillPatientId || '';
  const prefillPackageId = dataEl.dataset.prefillPackageId || '';

  const modal = document.getElementById('appointmentModal');
  const form = document.getElementById('appointmentForm');
  const modalTitle = document.getElementById('modalTitle');
  const modalSubtitle = document.getElementById('modalSubtitle');
  const modalError = document.getElementById('modalError');

  const appointmentId = document.getElementById('appointment_id');
  const patientId = document.getElementById('patient_id');
  const patientPackageId = document.getElementById('patient_package_id');
  const patientPackageItemId = document.getElementById('patient_package_item_id');
  const treatmentId = document.getElementById('treatment_id'); // oculto en Blade
  const specialistId = document.getElementById('specialist_id');
  const status = document.getElementById('status');
  const startAt = document.getElementById('start_at');
  const endAt = document.getElementById('end_at');
  const notes = document.getElementById('notes');

const btnCancelAppointment = document.getElementById('btnCancelAppointment');
const btnNewSameTimeAppointment = document.getElementById('btnNewSameTimeAppointment');
const btnDeleteAppointment = document.getElementById('btnDeleteAppointment');
const btnNoShow = document.getElementById('btnNoShow');
const btnCompleteAppointment = document.getElementById('btnCompleteAppointment');

  const submitBtn = form?.querySelector('button[type="submit"]');

  const initialSpecialistOptions = Array.from(specialistId?.options || []).map((opt) => ({
    value: opt.value,
    text: opt.textContent,
  }));

  function resetModalError() {
    modalError.textContent = '';
    modalError.classList.add('hidden');
  }

  function showModalError(message) {
    modalError.textContent = message;
    modalError.classList.remove('hidden');
  }

  function setBookingAvailability(enabled, message = '') {
    if (patientPackageId) {
      patientPackageId.disabled = !enabled;
    }

    if (patientPackageItemId) {
      patientPackageItemId.disabled = !enabled;
    }

    if (treatmentId) {
      treatmentId.disabled = !enabled;
      if (!enabled) {
        treatmentId.value = '';
      }
    }

    if (submitBtn) {
      submitBtn.disabled = !enabled;
      submitBtn.classList.toggle('opacity-50', !enabled);
      submitBtn.classList.toggle('cursor-not-allowed', !enabled);
    }

    if (message) {
      showModalError(message);
    } else {
      resetModalError();
    }
  }

  function openModal() {
    modal.classList.remove('hidden');
  }

  function closeModal() {
    modal.classList.add('hidden');
    resetModalError();
  }

  modal.addEventListener('click', (e) => {
    if (e.target?.dataset?.close === '1') closeModal();
  });

  document.querySelectorAll('[data-close="1"]').forEach((btn) => {
    btn.addEventListener('click', closeModal);
  });

  async function refreshAvailableSpecialists({ preserveValue = null } = {}) {
    if (!specialistId || !availableSpecialistsUrl || !startAt?.value || !endAt?.value) return;

    const start = new Date(startAt.value);
    const end = new Date(endAt.value);

    if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime()) || end <= start) {
      return;
    }

    const url = new URL(availableSpecialistsUrl, window.location.origin);
    url.searchParams.set('start_at', start.toISOString());
    url.searchParams.set('end_at', end.toISOString());

    if (appointmentId?.value) {
      url.searchParams.set('appointment_id', appointmentId.value);
    }

    try {
      const res = await fetch(url.toString(), {
        headers: { Accept: 'application/json' },
      });

      const items = await res.json();

      specialistId.innerHTML = '<option value="">— Selecciona —</option>';

      if (!Array.isArray(items) || items.length === 0) {
        showModalError('No hay especialistas disponibles para ese horario.');
        return;
      }

      // No limpiamos otros mensajes críticos de paquetes si existen.
      if (modalError.textContent === 'No hay especialistas disponibles para ese horario.') {
        resetModalError();
      }

      items.forEach((item) => {
        const opt = document.createElement('option');
        opt.value = item.id;
        opt.textContent = item.label || item.name;
        specialistId.appendChild(opt);
      });

      if (preserveValue && items.some((item) => String(item.id) === String(preserveValue))) {
        specialistId.value = String(preserveValue);
      } else if (
        specialistId.dataset.currentUserId &&
        items.some((item) => String(item.id) === String(specialistId.dataset.currentUserId))
      ) {
        specialistId.value = String(specialistId.dataset.currentUserId);
      } else if (items.length === 1) {
        specialistId.value = String(items[0].id);
      }
    } catch (error) {
      specialistId.innerHTML = initialSpecialistOptions
        .map((opt) => `<option value="${opt.value}">${opt.text}</option>`)
        .join('');
    }
  }

  async function loadPatientPackages(patientIdValue, preselectPackageId = null) {
    if (!patientPackageId) return false;

    patientPackageId.innerHTML = `<option value="">Cargando...</option>`;
    patientPackageId.disabled = true;

    if (patientPackageItemId) {
      patientPackageItemId.innerHTML = `<option value="">— Selecciona paquete primero —</option>`;
      patientPackageItemId.disabled = true;
    }

    if (!patientIdValue) {
      patientPackageId.innerHTML = `<option value="">— Selecciona paciente primero —</option>`;
      setBookingAvailability(false, 'Selecciona un paciente para cargar sus paquetes.');
      return false;
    }

    const url = patientPackagesUrlTemplate.replace('/0/', `/${patientIdValue}/`);

    try {
      const res = await fetch(url, {
        headers: { Accept: 'application/json' },
      });

      const items = await res.json();

      if (!Array.isArray(items) || items.length === 0) {
        patientPackageId.innerHTML = `<option value="">— Sin paquetes activos disponibles —</option>`;
        setBookingAvailability(
          false,
          'El paciente no tiene paquetes activos disponibles. Ve a Editar paciente para agregar uno nuevo.'
        );
        return false;
      }

      patientPackageId.innerHTML = `<option value="">— Selecciona —</option>`;

      for (const it of items) {
        const opt = document.createElement('option');
        opt.value = it.id;
        opt.textContent = it.label;
        patientPackageId.appendChild(opt);
      }

      patientPackageId.disabled = false;
      setBookingAvailability(true, '');

      if (preselectPackageId) {
        const exists = [...patientPackageId.options].some(
          (opt) => String(opt.value) === String(preselectPackageId)
        );

        if (exists) {
          patientPackageId.value = String(preselectPackageId);
          return true;
        }

        setBookingAvailability(
          false,
          'La cita estaba ligada a un paquete que ya no está disponible.'
        );
        return false;
      }

      return true;
    } catch (e) {
      patientPackageId.innerHTML = `<option value="">Error al cargar paquetes</option>`;
      setBookingAvailability(false, 'No se pudieron cargar los paquetes del paciente.');
      return false;
    }
  }

  async function loadPackageItems(packageIdValue, preselectItemId = null) {
    if (!patientPackageItemId) return false;

    patientPackageItemId.innerHTML = `<option value="">Cargando...</option>`;
    patientPackageItemId.disabled = true;

    if (treatmentId) {
      treatmentId.value = '';
    }

    if (!packageIdValue) {
      patientPackageItemId.innerHTML = `<option value="">— Selecciona paquete primero —</option>`;
      setBookingAvailability(false, 'Selecciona un paquete para cargar sus tratamientos disponibles.');
      return false;
    }

    const url = packageItemsUrlTemplate.replace('/0/', `/${packageIdValue}/`);

    try {
      const res = await fetch(url, {
        headers: { Accept: 'application/json' },
      });

      const items = await res.json();

      if (!Array.isArray(items) || items.length === 0) {
        patientPackageItemId.innerHTML = `<option value="">— Sin tratamientos disponibles —</option>`;
        setBookingAvailability(
          false,
          'El paquete no tiene tratamientos con sesiones disponibles.'
        );
        return false;
      }

      patientPackageItemId.innerHTML = `<option value="">— Selecciona —</option>`;

      for (const it of items) {
        const opt = document.createElement('option');
        opt.value = it.id;
        opt.textContent = it.label;
        opt.dataset.treatmentId = it.treatment_id;
        opt.dataset.duration = it.duration_minutes;
        opt.dataset.color = it.color_hex;
        patientPackageItemId.appendChild(opt);
      }

      patientPackageItemId.disabled = false;
      setBookingAvailability(true, '');

      if (preselectItemId) {
        const exists = [...patientPackageItemId.options].some(
          (opt) => String(opt.value) === String(preselectItemId)
        );

        if (exists) {
          patientPackageItemId.value = String(preselectItemId);
          patientPackageItemId.dispatchEvent(new Event('change'));
          return true;
        }

        setBookingAvailability(
          false,
          'La cita estaba ligada a un tratamiento del paquete que ya no tiene sesiones disponibles.'
        );
        return false;
      }

      return true;
    } catch (e) {
      patientPackageItemId.innerHTML = `<option value="">Error al cargar tratamientos</option>`;
      setBookingAvailability(false, 'No se pudieron cargar los tratamientos del paquete.');
      return false;
    }
  }

  function openNewAppointmentModal(startDate = null, endDate = null) {
    appointmentId.value = '';
    modalTitle.textContent = 'Nueva cita';
    modalSubtitle.textContent = 'Se guardará el nombre de quien agendó.';

btnCancelAppointment?.classList.add('hidden');
btnDeleteAppointment?.classList.add('hidden');
btnNoShow?.classList.add('hidden');
btnCompleteAppointment?.classList.add('hidden');
btnNewSameTimeAppointment?.classList.add('hidden');

    if (patientId) patientId.value = '';

    if (patientPackageId) {
      patientPackageId.innerHTML = `<option value="">— Selecciona paciente primero —</option>`;
      patientPackageId.value = '';
      patientPackageId.disabled = true;
    }

    if (patientPackageItemId) {
      patientPackageItemId.innerHTML = `<option value="">— Selecciona paquete primero —</option>`;
      patientPackageItemId.value = '';
      patientPackageItemId.disabled = true;
    }

    if (treatmentId) {
      treatmentId.value = '';
      treatmentId.disabled = true;
    }

    if (specialistId) {
      specialistId.innerHTML = initialSpecialistOptions
        .map((opt) => `<option value="${opt.value}">${opt.text}</option>`)
        .join('');
    }

    status.value = 'confirmed';
    notes.value = '';

    if (startDate && endDate) {
      startAt.value = toLocalInputValue(startDate);
      endAt.value = toLocalInputValue(endDate);
    } else {
      setStartEndNowSuggestion(startAt, endAt);
    }

    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }

    resetModalError();
    openModal();

    refreshAvailableSpecialists({
      preserveValue: specialistId?.dataset?.currentUserId || null,
    });
  }

  if (patientId && patientPackageId) {
    patientId.addEventListener('change', async () => {
      await loadPatientPackages(patientId.value);
    });
  }

  if (patientPackageId && patientPackageItemId) {
    patientPackageId.addEventListener('change', async () => {
      await loadPackageItems(patientPackageId.value);
    });

    patientPackageItemId.addEventListener('change', () => {
      const opt = patientPackageItemId.options[patientPackageItemId.selectedIndex];
      const treatmentIdFromItem = opt?.dataset?.treatmentId;
      const duration = Number(opt?.dataset?.duration || 0);

      if (treatmentIdFromItem && treatmentId) {
        treatmentId.value = String(treatmentIdFromItem);
      } else if (treatmentId) {
        treatmentId.value = '';
      }

      if (duration && startAt?.value && endAt) {
        const startDate = new Date(startAt.value);
        const suggestedEnd = addMinutes(startDate, duration);
        endAt.value = toLocalInputValue(suggestedEnd);
        refreshAvailableSpecialists({ preserveValue: specialistId.value || null });
      }
    });
  }

  if (treatmentId) {
    treatmentId.addEventListener('change', () => {
      if (treatmentId.disabled) return;

      const opt = treatmentId.options[treatmentId.selectedIndex];
      const duration = Number(opt?.dataset?.duration || 0);
      if (!duration) return;

      if (startAt?.value && endAt) {
        const startDate = new Date(startAt.value);
        const suggestedEnd = addMinutes(startDate, duration);
        endAt.value = toLocalInputValue(suggestedEnd);
        refreshAvailableSpecialists({ preserveValue: specialistId.value || null });
      }
    });
  }

  startAt?.addEventListener('change', () => {
    refreshAvailableSpecialists({ preserveValue: specialistId.value || null });
  });

  endAt?.addEventListener('change', () => {
    refreshAvailableSpecialists({ preserveValue: specialistId.value || null });
  });

  const calendar = new Calendar(calendarEl, {
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    locales: [esLocale],
    locale: 'es',
    initialView: 'timeGridWeek',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay',
    },
    allDaySlot: false,
    selectable: true,
    editable: true,
    nowIndicator: true,
        slotEventOverlap: false,
    eventMaxStack: 3,
    slotMinTime: '07:00:00',
    slotMaxTime: '22:00:00',
    events: eventsUrl,

    selectAllow: (selectInfo) => {
      return selectInfo.start >= new Date();
    },

    eventAllow: (dropInfo) => {
      return dropInfo.start >= new Date();
    },

    select: async (info) => {
      if (info.start < new Date()) return;
      openNewAppointmentModal(info.start, info.end);
    },

    eventClick: async (info) => {
      const ev = info.event;
      const currentSpecialistId = ev.extendedProps?.specialist_id ?? null;

      appointmentId.value = ev.id;
      modalTitle.textContent = 'Editar cita';
      modalSubtitle.textContent = ev.extendedProps?.creator_name
        ? `Agendada por: ${ev.extendedProps.creator_name}`
        : 'Editar información de la cita.';

btnCancelAppointment?.classList.remove('hidden');
btnNewSameTimeAppointment?.classList.remove('hidden');
btnDeleteAppointment?.classList.remove('hidden');
btnNoShow?.classList.remove('hidden');
btnCompleteAppointment?.classList.remove('hidden');

      patientId.value = ev.extendedProps?.patient_id ?? '';
      status.value = ev.extendedProps?.status ?? 'confirmed';
      notes.value = ev.extendedProps?.notes ?? '';

      startAt.value = toLocalInputValue(ev.start);
      endAt.value = toLocalInputValue(ev.end || ev.start);

      let packageLoaded = true;

      if (patientPackageId) {
        packageLoaded = await loadPatientPackages(
          patientId.value,
          ev.extendedProps?.patient_package_id ?? null
        );
      }

      if (packageLoaded && patientPackageId?.value) {
        await loadPackageItems(
          patientPackageId.value,
          ev.extendedProps?.patient_package_item_id ?? null
        );
      }

      if (treatmentId) {
        treatmentId.value = ev.extendedProps?.treatment_id ?? '';
      }

      await refreshAvailableSpecialists({
        preserveValue: currentSpecialistId,
      });

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
      patient_package_id: ev.extendedProps?.patient_package_id ?? null,
      patient_package_item_id: ev.extendedProps?.patient_package_item_id ?? null,
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
        alert(data?.message || 'No se pudo actualizar. Revisa empalmes o paquetes.');
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

    if (patientId.value && (!patientPackageId?.value || patientPackageId.disabled)) {
      setBookingAvailability(
        false,
        'El paciente no tiene paquetes activos disponibles. Ve a Editar paciente para agregar uno nuevo.'
      );
      return;
    }

    if (patientPackageId?.value && (!patientPackageItemId?.value || patientPackageItemId.disabled)) {
      setBookingAvailability(
        false,
        'Debes seleccionar un tratamiento disponible dentro del paquete.'
      );
      return;
    }

    if (!specialistId?.value) {
      showModalError('Debes seleccionar un especialista disponible.');
      return;
    }

    const payload = {
      patient_id: patientId.value || null,
      patient_package_id: patientPackageId?.value || null,
      patient_package_item_id: patientPackageItemId?.value || null,
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
        showModalError(data?.message || 'Error al guardar. Verifica datos.');
        return;
      }

      closeModal();
      calendar.unselect();
      calendar.refetchEvents();
    } catch (err) {
      showModalError('Error de red. Intenta nuevamente.');
    }
  });

  if (btnOpenNewAppointment) {
    btnOpenNewAppointment.addEventListener('click', () => {
      openNewAppointmentModal();
    });
  }


btnNewSameTimeAppointment?.addEventListener('click', async () => {
    const sameStart = startAt.value;
    const sameEnd = endAt.value;

    appointmentId.value = '';

    modalTitle.textContent = 'Nueva cita';
    modalSubtitle.textContent = 'Nueva cita en el mismo horario con otro especialista disponible.';

    btnCancelAppointment?.classList.add('hidden');
    btnNewSameTimeAppointment?.classList.add('hidden');
    btnNoShow?.classList.add('hidden');
    btnCompleteAppointment?.classList.add('hidden');

    patientId.value = '';
    status.value = 'confirmed';
    notes.value = '';

    startAt.value = sameStart;
    endAt.value = sameEnd;

    if (patientPackageId) {
        patientPackageId.innerHTML = `— Selecciona paciente primero —`;
        patientPackageId.value = '';
        patientPackageId.disabled = true;
    }

    if (patientPackageItemId) {
        patientPackageItemId.innerHTML = `— Selecciona paquete primero —`;
        patientPackageItemId.value = '';
        patientPackageItemId.disabled = true;
    }

    if (treatmentId) {
        treatmentId.value = '';
        treatmentId.disabled = true;
    }

    await refreshAvailableSpecialists();

    resetModalError();
});



btnDeleteAppointment?.addEventListener('click', async () => {
    const id = appointmentId.value;

    if (!id) return;

    const confirmed = confirm(
        '¿Eliminar definitivamente esta cita?\n\nEsta acción no la marcará como cancelada. La cita se borrará completamente de la agenda y no podrás recuperarla.'
    );

    if (!confirmed) return;

    try {
        const url = destroyUrlTemplate.replace('__ID__', id);

        const res = await fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrf,
                Accept: 'application/json',
            },
        });

        const data = await res.json().catch(() => ({}));

        if (!res.ok) {
            showModalError(data?.message || 'No se pudo eliminar la cita.');
            return;
        }

        closeModal();
        calendar.refetchEvents();
    } catch (e) {
        showModalError('Error de red al eliminar la cita.');
    }
});
  btnCancelAppointment?.addEventListener('click', async () => {
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
        showModalError(data?.message || 'No se pudo cancelar la cita.');
        return;
      }

      closeModal();
      calendar.refetchEvents();
    } catch (e) {
      showModalError('Error de red al cancelar.');
    }
  });

  btnNoShow?.addEventListener('click', async () => {
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
        showModalError(data?.message || 'No se pudo marcar la inasistencia.');
        return;
      }

      closeModal();
      calendar.refetchEvents();
    } catch (e) {
      showModalError('Error de red al marcar inasistencia.');
    }
  });

  btnCompleteAppointment?.addEventListener('click', async () => {
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
        showModalError(data?.message || 'No se pudo marcar como realizada.');
        return;
      }

      closeModal();
      calendar.refetchEvents();
    } catch (e) {
      showModalError('Error de red al marcar como realizada.');
    }
  });

  async function prefillFromPatients() {
    if (!prefillPatientId) return;

    appointmentId.value = '';
    modalTitle.textContent = 'Nueva cita';
    modalSubtitle.textContent = 'Se guardará el nombre de quien agendó.';

btnCancelAppointment?.classList.add('hidden');
btnDeleteAppointment?.classList.add('hidden');
btnNoShow?.classList.add('hidden');
btnCompleteAppointment?.classList.add('hidden');

    status.value = 'confirmed';
    notes.value = '';

    setStartEndNowSuggestion(startAt, endAt);

    patientId.value = String(prefillPatientId);

    const packageLoaded = await loadPatientPackages(patientId.value, prefillPackageId || null);

    if (packageLoaded && patientPackageId?.value) {
      await loadPackageItems(patientPackageId.value);
    }

    await refreshAvailableSpecialists({
      preserveValue: specialistId?.dataset?.currentUserId || null,
    });

    openModal();
  }

  prefillFromPatients();
});