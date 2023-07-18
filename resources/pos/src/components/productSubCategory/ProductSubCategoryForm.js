import React, {createRef, useState, useEffect} from 'react';
import {connect} from 'react-redux';
import {Form, Modal} from 'react-bootstrap-v5';
import {
    editProductSubCategory, fetchProductSubCategory, fetchProductSubCategories, addProductSubCategory
} from '../../store/action/productSubCategoryAction';
// import user from '../../assets/images/productCategory_logo.jpeg';
import {getFormattedMessage, placeholderText} from '../../shared/sharedMethod';
import ModelFooter from '../../shared/components/modelFooter';
import apiConfig from '../../config/apiConfig';
import ReactSelect from '../../shared/select/reactSelect';

const ProductSubCategoryForm = (props) => {
    const {handleClose, show, title, editProductSubCategory, singleProductSubCategory, addProductSubCategory} = props;
    const innerRef = createRef();
    const [productSubCategoryValue, setProductSubCategoryValue] = useState({
        name: singleProductSubCategory ? singleProductSubCategory.name : '',
        product_category_id: singleProductSubCategory ? singleProductSubCategory.product_category_id : '',
    });
    const [errors, setErrors] = useState({
        name: '',
        selectedCategory: ''
    });
    // const editImg = singleProductSubCategory ? singleProductSubCategory.image : user;
    const [selectImg, setSelectImg] = useState(null);
    const disabled = selectImg ? false : singleProductSubCategory && singleProductSubCategory.name === productSubCategoryValue.name.trim();

    const [productCategories, setProductCategories] = useState(null);
    const [selectedCategory, setSelectedCategory] = useState(null);
    // const [selectedCategory] = useState(singleProductSubCategory && singleProductSubCategory[0] ? ([{
    //     label: singleProductSubCategory[0].product_subcategory_id.label, value: singleProductSubCategory[0].product_subcategory_id.value
    // }]) : null) : useState(null);
    var options = null;
    const [opts, setOpts] = useState([{}]);

    console.log('singleProductSubCategory' , singleProductSubCategory)
    useEffect(() => {
        const fetchData = async () => {
          try {
            const response = await apiConfig.get(`product-categories?page[size]=0`);
            setProductCategories(response.data.data);
            if(singleProductSubCategory) {
                const selectedCatObj = response.data.data.filter((obj) => singleProductSubCategory.product_category_id === obj.id);
                console.log('selectedCatObj', selectedCatObj)
            //     if (selectedCatObj) {
            //         console.log('if worked')
            //         // const { id, attributes: { name } } = selectedCatObj[0];
            //         const categoryOption = { value: selectedCatObj[0].id, label: selectedCatObj[0].attributes.name };
            //         console.log('categoryOption', categoryOption)
            //         // const updatedSelectedCategory = [{ label: categoryOption.label, value: categoryOption.value }];
            //         // console.log('updatedSelectedCategory', updatedSelectedCategory)
            //         // selectedCategory = updatedSelectedCategory;
            //         selectedCategory = categoryOption;
            //       }
            //     console.log(selectedCategory, 'newunit')
            //     // setSelectedCategory(selectedCatObj);
            }
          } catch (error) {
            console.error('Error:', error);
          }
        };

        fetchData();
    
    }, []);

    useEffect(() => {
        const fetchData = async () => {
            options = productCategories && productCategories.map((productCategory) => ({
                value: productCategory.id,
                label: productCategory.attributes.name,
            }));
        };
        
        fetchData();
        setOpts(options)

    }, [productCategories]);

    const handleValidation = () => {
        let errorss = {};
        let isValid = false;
        if (!productSubCategoryValue['name'].trim()) {
            errorss['name'] = getFormattedMessage('globally.input.name.validate.label');
        }
        else if ((productSubCategoryValue['name'] && productSubCategoryValue['name'].length > 50)) {
            errorss['name'] = getFormattedMessage('brand.input.name.valid.validate.label');
        }
        else if (!selectedCategory) {
            errorss['product_category_id'] = getFormattedMessage('globally.input.product-category.validate.label');
        }
        else {
            isValid = true;
        }
        setErrors(errorss);
        return isValid;
    };

    const onChangeInput = (e) => {
        e.preventDefault();
        setProductSubCategoryValue(inputs => ({...inputs, [e.target.name]: e.target.value}))
        setErrors('');
    };

    const prepareFormData = (data) => {
        const formData = new FormData();
        formData.append('name', data.name);
        if(selectedCategory) {
            formData.append('product_category_id', selectedCategory.value);
        }
        return formData;
    };

    const onSubmit =  (event) => {
        event.preventDefault();
        const valid = handleValidation();
        if (singleProductSubCategory && valid) {
            if (!disabled) {
                editProductSubCategory(singleProductSubCategory.id, prepareFormData(productSubCategoryValue), handleClose);
                clearField(false);
            }
        } else {
            if (valid) {
                setProductSubCategoryValue(productSubCategoryValue);
                addProductSubCategory(prepareFormData(productSubCategoryValue));
                clearField(false);
            }
        }
    };

    const clearField = () => {
        setProductSubCategoryValue({
            name: '',
        });
        setErrors('');
        handleClose(false);
    };

    const onProductCategoryChange = (selectedOption) => {
        setSelectedCategory(selectedOption);
        setErrors('');
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
                        singleProductSubCategory ? onEdit(e) : onSubmit(e)
                    }
                }}>
                    <Modal.Header closeButton>
                        <Modal.Title>{title}</Modal.Title>
                    </Modal.Header>
                    <Modal.Body>
                    <div className='row'>
                        <div className='col-md-12'>
                            <label className='form-label'>{getFormattedMessage('globally.input.name.label')}: </label>
                            <span className='required'/>
                            <input type='text' name='name'
                                placeholder={placeholderText('globally.input.name.placeholder.label')}
                                className='form-control' ref={innerRef} autoComplete='off'
                                onChange={(e) => onChangeInput(e)}
                                value={productSubCategoryValue.name}/>
                            <span className='text-danger d-block fw-400 fs-small mt-2'>{errors['name'] ? errors['name'] : null}</span>
                        </div>
                        <div className='col-md-12'>
                            <ReactSelect title={getFormattedMessage('product.input.product-category.label')}
                                placeholder={placeholderText('product.input.product-category.placeholder.label')}
                                defaultValue={selectedCategory}
                                // options = {opts}
                                value={selectedCategory}
                                onChange={onProductCategoryChange}
                                data={productCategories}
                                errors={errors['product_category_id']}
                            />
                        </div>
                    </div>
                </Modal.Body>
            </Form>
            <ModelFooter onEditRecord={singleProductSubCategory} onSubmit={onSubmit} editDisabled={disabled}
                clearField={clearField} addDisabled={!productSubCategoryValue.name.trim()}/>
        </Modal>
    )
};

export default connect(null, {fetchProductSubCategory, editProductSubCategory, fetchProductSubCategories, addProductSubCategory})(ProductSubCategoryForm);
