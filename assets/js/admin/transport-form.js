const templateAlias = document.getElementById('template-form-alias');
const templateCrewMember = document.getElementById('template-form-crew-member');
const containerAlias = document.getElementById('container-alias-fields');
const containerCrewMember = document.getElementById('container-crew-member-fields');

function addAliasField() {
    const newId = 'alias_index_' + Math.random().toString(36).substring(2, 15);
    const field = templateAlias.querySelector('.alias_input_form').cloneNode(true);

    field.id = newId;
    field.querySelector('button').dataset.target = newId;

    containerAlias.appendChild(field);
}

function addCrewMemberField(name = '', role = '', contact = '', license = '') {
    const newId = Math.random().toString(36).substring(2, 15);
    const totalForms = containerCrewMember.querySelectorAll('.crew_member_input_form').length;
    let field = templateCrewMember.querySelector('.crew_member_input_form').outerHTML;
    field = field.replaceAll('{{ID}}', newId);
    field = field.replaceAll('{{INDEX}}', totalForms);
    
    // Crear el contenedor y agregar al DOM primero
    const container = document.createElement('div');
    container.innerHTML = field;
    containerCrewMember.appendChild(container.firstElementChild);
    
    // Ahora buscar y establecer valores en el DOM real
    const nameInput = containerCrewMember.querySelector(`input[name="crew[${totalForms}][name]"]:last-of-type`);
    const roleInput = containerCrewMember.querySelector(`input[name="crew[${totalForms}][role]"]:last-of-type`);
    const contactInput = containerCrewMember.querySelector(`input[name="crew[${totalForms}][contact]"]:last-of-type`);
    const licenseInput = containerCrewMember.querySelector(`input[name="crew[${totalForms}][license]"]:last-of-type`);
    
    if (nameInput) {
        nameInput.value = name;
    }
    if (roleInput) {
        roleInput.value = role;
    }
    if (contactInput) {
        contactInput.value = contact;
    }
    if (licenseInput) {
        licenseInput.value = license;
    }
}

function removeAliasField(buttonRemove) {
    const field = document.getElementById(buttonRemove.dataset.target);
    if (field) {
        field.remove();
    }
}

function removeCrewMemberField(buttonRemove) {
    const field = document.getElementById(buttonRemove.dataset.target);
    if (field) {
        field.remove();
        reorganizeCrewMemberIndices();
    }
}

function reorganizeCrewMemberIndices() {
    const crewMemberForms = containerCrewMember.querySelectorAll('.crew_member_input_form');
    crewMemberForms.forEach((form, index) => {
        const indexForm = form.dataset.index;
        const nameInput = form.querySelector(`input[name="crew[${indexForm}][name]"]`);
        const roleInput = form.querySelector(`input[name="crew[${indexForm}][role]"]`);
        const contactInput = form.querySelector(`input[name="crew[${indexForm}][contact]"]`);
        const licenseInput = form.querySelector(`input[name="crew[${indexForm}][license]"]`);
        if (nameInput) {
            nameInput.setAttribute('name', `crew[${index}][name]`);
        }
        if (roleInput) {
            roleInput.setAttribute('name', `crew[${index}][role]`);
        }
        if (contactInput) {
            contactInput.setAttribute('name', `crew[${index}][contact]`);
        }
        if (licenseInput) {
            licenseInput.setAttribute('name', `crew[${index}][license]`);
        }
        form.dataset.index = index;
    });
}

transportFormData.crewMembers.forEach((crewMember) => {
    console.log(crewMember);
    
    addCrewMemberField(crewMember.name, crewMember.role, crewMember.contact, crewMember.license);
});