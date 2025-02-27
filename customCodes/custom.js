function sub_buttonpress(){

    
    const Name = contacts.txtName.value
    const Email = contacts.txtEmail.value
    const comments = contacts.txtComments.value
    const means = contacts.Means.value

    if (Name === '' || number === '' || Email === '') {
        // Display error message or handle the situation as per your requirement 
        exampleModalLabel.textContent='Enter your Contact Information';
        end.textContent="Please be sure to complete the required information to proceed";
      }

    else{
        modalName.textContent = `Name: ${Name}`;
        modalEmail.textContent = `Email: ${Email}`;
        modalNumber.textContent = `Comment: ${comments}`; 
        modalMeans.textContent = `Means: ${means}`;
        exampleModalLabel.textContent='Thanks for Contacting us';
        end.textContent="We will be back with you shortly";
      }    
    contacts.txtName.value = ''
    contacts.txtEmail.value = ''
    contacts.txtComments.value = ''
    contacts.Means.value = ''
    }
    
