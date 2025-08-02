import React, {Fragment, useState} from 'react';
import Header from 'components/shared/Header.js';
import TextField from 'components/shared/TextField.js';
import Api from 'root/api.js';
import ModalInfo from 'components/shared/ModalInfo.js';

const ChangePassword = () => {
    const [oldPassword, setOldPassword] = useState("");
    const [newPassword, setNewPassword] = useState("");
    const [repeatNewPassword, setRepeatNewPassword] = useState("");
    const [showModal, setShowModal] = useState(false);
    const [modalError, setModalError] = useState({show: false, message: ""});

    const handleSubmit = async() => {
        if(newPassword !== repeatNewPassword ) {
            setModalError({show: true, message: "Ulangi password tidak sama dengan password baru!"});
            setTimeout(() => {
                setModalError({show: false, message: ""});
            }, 2000);
            return;
        }
        
        const response = await Api('/api/change_password', {old_password: oldPassword, new_password: newPassword}, 'POST');
        if(response){
            setShowModal(true);
            setTimeout(() => {
                setShowModal(false);
            }, 2000);
        }else{
            setModalError({show: true, message: "Password yang anda masukkan salah!"});
            setTimeout(() => {
                setModalError({show: false, message: ""});
            }, 2000);
        }   
    }
    
    return (
        <Fragment>
            <Header
                title='Ubah Password'
                action={ {title: "submit", onClick: () => {handleSubmit()} } }
            />
            <div className="p-4">
                <TextField
                    label="Password Lama"
                    type="password"
                    onChange={(e) => {setOldPassword(e.target.value)}}
                />
                <TextField
                    label="Password Baru"
                    type="password"
                    onChange={(e) => {setNewPassword(e.target.value)}}
                />
                <TextField
                    label="Ulangi Password Baru"
                    type="password"
                    onChange={(e) => {setRepeatNewPassword(e.target.value)}}
                />
            </div>
            {showModal && 
                <ModalInfo text={"Berhasil"} subText={"Password telah diubah"}/>
            }
            {modalError.show && 
                <ModalInfo text={"Gagal"} subText={modalError.message} type="error"/>
            }
        </Fragment>
    );
}

export default ChangePassword;