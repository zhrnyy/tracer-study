

<script>

let showThisFieldIf = {
    bekerja_waktu_tunggu: {
        status: [1],
    },
    bekerja_penghasilan: {
        status: [1],
    },
    bekerja_lokasi: {
        status: [1],
    },
    bekerja_provinsi: {
        status: [1],
    },
    bekerja_kabupaten: {
        status: [1],
    },
    bekerja_jenis_perusahaan: {
        status: [1],
    },
    bekerja_nama_perusahaan: {
        status: [1],
    },
    belum_sebelum_lulus: {
        status: [2],
    },
    belum_setelah_lulus: {
        status: [2],
    },
    belum_cara_mencari_pekerjaan: {
        status: [2],
    },
    belum_banyak_instansi: {
        status: [2],
    },
    belum_aktif_4_minggu: {
        status: [2],
    },
    melanjutkanPendidikan: {
        status: [4],
    },
    mencariKerja: {
        status: [5],
    },

    
}


document.addEventListener('DOMContentLoaded', conditionalFormFieldFunc);
document.addEventListener('DOMContentLoaded',function(){
jQuery(document).on('elementor/popup/show', (event, id, instance) => {
conditionalFormFieldFunc();
});
});

function conditionalFormFieldFunc() {
function testLogic() {
for (const [conditionalInputID, condition] of Object.entries(showThisFieldIf)) {
let conditionalInput = setInputsElemArray(conditionalInputID);
let match = true;
for (const [conditionID, conditionValues] of Object.entries(condition)) {
let inputs = setInputsElemArray(conditionID);
let selectedInputs = [];
inputs.forEach((input, i) => { if (input.checked) { selectedInputs.push(i); } });
if (inputs[0].tagName == 'SELECT') {
selectedInputs.push(inputs[0].selectedIndex);
}
let adjustedConditionValues = conditionValues.map(e => e - 1);
if (!(adjustedConditionValues.every(condition => selectedInputs.indexOf(condition) > -1))) {
match = false;
}
};
if (match) {
conditionalInput.forEach(e => e.closest('.elementor-field-group').style.display = "block")
} else {
conditionalInput.forEach(e => e.closest('.elementor-field-group').style.display = "none")
}
}
}
testLogic();

/* Add event listeners */
for (const [conditionalInputID, condition] of Object.entries(showThisFieldIf)) {
for (const [conditionID, conditionValues] of Object.entries(condition)) {
let inputs = setInputsElemArray(conditionID);
inputs.forEach(input => {
input.addEventListener('input', function () {
testLogic();
})
})
}
}

function setInputsElemArray(ID) {
let selectors = `[name="form_fields[${ID}]"]`;
let inputs = Array.from(document.querySelectorAll(selectors));
if (!inputs.length) {
selectors = `[name="form_fields[${ID}][]"]`;
inputs = Array.from(document.querySelectorAll(selectors));
}
return inputs;
}
};

</script>
