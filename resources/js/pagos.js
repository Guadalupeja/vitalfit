document.addEventListener('DOMContentLoaded', () => {
  const patientSelect = document.querySelector('select[name="patient_id"]');
  const pkgSelect = document.getElementById('patient_treatment_id');

  if (!patientSelect || !pkgSelect) return;

  const template = pkgSelect.dataset.templateUrl; // .../pacientes/0/paquetes

  function getParams() {
    const url = new URL(window.location.href);
    return {
      patient_id: url.searchParams.get('patient_id') || '',
      patient_treatment_id: url.searchParams.get('patient_treatment_id') || '',
    };
  }

  async function loadPackages(patientId, preselectPkgId = '') {
    pkgSelect.innerHTML = `<option value="">Cargando...</option>`;

    if (!patientId) {
      pkgSelect.innerHTML = `<option value="">— Selecciona paciente primero —</option>`;
      return;
    }

    const url = template.replace('/0/', `/${patientId}/`);

    try {
      const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
      const items = await res.json();

      if (!Array.isArray(items) || items.length === 0) {
        pkgSelect.innerHTML = `<option value="">— Sin paquetes activos —</option>`;
        return;
      }

      pkgSelect.innerHTML = `<option value="">— Selecciona —</option>`;
      for (const it of items) {
        const opt = document.createElement('option');
        opt.value = it.id;
        opt.textContent = it.label;
        pkgSelect.appendChild(opt);
      }

      if (preselectPkgId) {
        pkgSelect.value = String(preselectPkgId);
      }
    } catch (e) {
      pkgSelect.innerHTML = `<option value="">Error al cargar paquetes</option>`;
    }
  }

  // cargar por cambio manual
  patientSelect.addEventListener('change', () => {
    loadPackages(patientSelect.value);
  });

  // preselección por query params
  const params = getParams();
  if (params.patient_id) {
    patientSelect.value = params.patient_id;
    loadPackages(params.patient_id, params.patient_treatment_id);
  }
});
