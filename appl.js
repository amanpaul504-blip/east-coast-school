document.getElementById('myForm').addEventListener('submit', function(event) {
    let isValid = true;
    const requiredFields = ['firstname','middlename','lastname','DoB','sex','height','nationality','pnumber','lang','email','pnumber_a','hnumber','street','t_city','country','cu_study','country_a','c_studied','yenrolled','sID_num','d_graduated','n_referee','n_univ','country_b','pnumber_b','email_b','fapplied','duration','d_start'];
    const allowedExtensions = /(\.jpg|\.jpeg|\.png|\.pdf)$/i;

    // Check required text fields
    requiredFields.forEach(id => {
        const field = document.getElementsByName(id)[0];
        if (!field.value.trim()) {
            alert(id.charAt(0).toUpperCase() + id.slice(1) + " is required.");
            isValid = false;
        }
    });

    // Check file uploads
    const fileInputs = ["fileUpload"];
    fileInputs.forEach(name => {
        const fileInput = document.getElementsByName(name)[0];
        if (fileInput.files.length > 0) {
            if (!allowedExtensions.exec(fileInput.value)) {
                alert('Invalid file type for ' + name + '. Please upload JPG, PNG, or JPEG');
                fileInput.value = '';
                isValid = false;
            }
        const fileInputs = ["fileUpload1", "fileUpload2", "fileUpload3"];
    fileInputs.forEach(name => {
        const fileInput = document.getElementsByName(name)[0];
        if (fileInput.files.length > 0) {
            if (!allowedExtensions.exec(fileInput.value)) {
                alert('Invalid file type for ' + name + '. Please upload PDF.');
                fileInput.value = '';
                isValid = false;
            }
    };

    if (!isValid) {
        event.preventDefault(); // Stop form from submitting if errors found
    }
});
