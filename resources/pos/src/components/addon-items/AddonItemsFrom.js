import React, {useState, createRef} from 'react';
import {connect} from 'react-redux';
import {Form, Modal} from 'react-bootstrap-v5';
import {editAddonItem, fetchAddonItems} from '../../store/action/addonItemsAction';
import ImagePicker from '../../shared/image-picker/ImagePicker';
import user from '../../assets/images/brand_logo.png';
import {getFormattedMessage} from '../../shared/sharedMethod';
import {placeholderText} from '../../shared/sharedMethod';
import ModelFooter from '../../shared/components/modelFooter';

const AddonItemsFrom = (props) => {
    const {handleClose, show, title, addAddonItemData, editAddonItem, singleAddonItem} = props;
    const innerRef = createRef();
    const [formValue, setFormValue] = useState({
        name: singleAddonItem ? singleAddonItem.name : '',
        image: singleAddonItem ? singleAddonItem.image : '',
        slug: singleAddonItem ? singleAddonItem.slug : '',
        description: singleAddonItem ? singleAddonItem.description : '',
    });
    const [errors, setErrors] = useState({name: ''});

    const editImg = singleAddonItem ? singleAddonItem.image : user;
    const newImg = formValue.image === false ? user : editImg;
    const [imagePreviewUrl, setImagePreviewUrl] = useState(newImg);
    const [selectImg, setSelectImg] = useState(null);

    const disabled = selectImg ? false : singleAddonItem && singleAddonItem.name === formValue.name.trim();

    const handleImageChanges = (e) => {
        e.preventDefault();
        if (e.target.files.length > 0) {
            const file = e.target.files[0];
            if (file.type === 'image/jpeg' || file.type === 'image/png') {
                setSelectImg(file);
                const fileReader = new FileReader();
                fileReader.onloadend = () => {
                    setImagePreviewUrl(fileReader.result);
                };
                fileReader.readAsDataURL(file);
                setErrors('');
            }
        }
    };

    const handleValidation = () => {
        let errorss = {};
        let isValid = false;
        if (!formValue['name'].trim()) {
            errorss['name'] = getFormattedMessage('globally.input.name.validate.label');
        }
        else if ((formValue['name'] && formValue['name'].length > 50)) {
            errorss['name'] = getFormattedMessage('brand.input.name.valid.validate.label');
        }
        else if (!formValue['description'].trim()) {
            errorss['description'] = getFormattedMessage('globally.input.description.validate.label');
        }
        else {
            isValid = true;
        }
        setErrors(errorss);
        return isValid;
    };

    const onChangeInput = (e) => {
        e.preventDefault();
        setFormValue(inputs => ({...inputs, [e.target.name]: e.target.value}));
        if(e.target.name == 'name'){
          changeSlugValue(e.target.value);
        }
        setErrors('');
    };

    const changeSlugValue = (value) => {
       const slug =  createSlug(value)
        setFormValue(inputs => ({...inputs, ['slug']: slug}));
    }

    const createSlug =(str) => {
        return str
            .toLowerCase()
            .replace(/[^\w\s]/gi, '')
            .replace(/\s+/g, '-')
    }

    const prepareFormData = (data) => {
        const formData = new FormData();
        formData.append('name', data.name);
        formData.append('slug', data.slug);
        formData.append('description', data.description);
        if (selectImg) {
            formData.append('image', data.image);
        }
        return formData;
    };

    const onSubmit = (event) => {
        event.preventDefault();
        const valid = handleValidation();
        formValue.image = selectImg;
        if (singleAddonItem && valid) {
            if (!disabled) {
                formValue.image = selectImg;
                editAddonItem(singleAddonItem.id, prepareFormData(formValue), handleClose);
                clearField(false);
            }
        } else {
            if (valid) {
                setFormValue(formValue);
                addAddonItemData(prepareFormData(formValue));
                clearField(false);
            }
        }
        setSelectImg(null);
    };

    const clearField = () => {
        setFormValue({
            name: '',
            slug: '',
            description: '',
            image: ''
        })
        setImagePreviewUrl(user);
        setErrors('');
        handleClose(false);
    };

    return (
        <Modal show={show}
               onHide={clearField}
               keyboard={true}
               onShow={() => setTimeout(() => {
                   innerRef.current.focus();
               }, 1)}
        >
            <Form onKeyPress={(e) => {
                if (e.key === 'Enter') {
                    onSubmit(e)
                }
            }}>
                <Modal.Header closeButton>
                    <Modal.Title>{title}</Modal.Title>
                </Modal.Header>
                <Modal.Body>
                    <div className='row'>
                        <div className='col-md-12 mb-5'>
                            <label
                                className='form-label'>{getFormattedMessage('globally.input.name.label')}: </label>
                            <span className='required'/>
                            <input type='text' name='name' autoComplete='off'
                                   placeholder={placeholderText('globally.input.name.placeholder.label')}
                                   className='form-control' ref={innerRef} value={formValue.name}
                                   onChange={(e) => onChangeInput(e)}/>
                            <span className='text-danger d-block fw-400 fs-small mt-2'>
                                        {errors['name'] ? errors['name'] : null}
                                </span>
                        </div>
                        <div className='col-md-12'>
                            <input type='text' name='slug'
                                   className='form-control' ref={innerRef} value={formValue.slug}
                                   hidden={true}
                            />
                        </div>
                        <div className='col-md-12 mb-5'>
                            <label
                                className='form-label'>{getFormattedMessage('globally.input.description.label')}: </label>
                            <span className='required'/>
                            <textarea name='description' placeholder={placeholderText('globally.input.description.placeholder.label')}
                                      className='form-control' ref={innerRef} value={formValue.description}
                                      onChange={(e) => onChangeInput(e)}>
                            </textarea>
                            <span className='text-danger d-block fw-400 fs-small mt-2'>
                                        {errors['description'] ? errors['description'] : null}
                            </span>
                        </div>
                    </div>
                </Modal.Body>
            </Form>
            <ModelFooter onEditRecord={singleAddonItem} onSubmit={onSubmit} editDisabled={disabled}
                         clearField={clearField} addDisabled={!formValue.name.trim()}/>
        </Modal>
    )
};

export default connect(null, {fetchAddonItems, editAddonItem})(AddonItemsFrom);
