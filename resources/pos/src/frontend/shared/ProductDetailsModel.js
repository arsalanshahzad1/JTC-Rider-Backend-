/// new custom product-detail-modal

import React, {useEffect, useState} from 'react';
import {Modal, Button} from 'react-bootstrap';
import {Form, InputGroup} from 'react-bootstrap-v5';
import Select from 'react-select';
import {connect} from 'react-redux';
import {decimalValidate, getFormattedMessage, placeholderText, getFormattedOptions} from '../../shared/sharedMethod';
import {productUnitDropdown} from '../../store/action/productUnitAction';
import ReactSelect from '../../shared/select/reactSelect';
import {calculateProductCost} from './SharedMethod';
import {taxMethodOptions, discountMethodOptions} from '../../constants';
// import {log} from "echarts/types/src/util/log";
const ProductDetailsModel = (props) => {
    const {
        openProductDetailModal,
        isOpenCartItemUpdateModel,
        cartProduct,
        onProductUpdateInCart,
        productModelId,
        updateCost,
        productUnitDropdown,
        productUnits,
        frontSetting
    } = props;

    const [product, setProduct] = useState(cartProduct);
    const [unitPrice, setUnitPrice] = useState(0);
    const [saleUnitType, setSaleUnitType] = useState(null);
    const [discount, setDiscount] = useState('0.00');
    const [orderTax, setOrderTax] = useState(product.tax_value);
    const [addons, setAddons] = useState(product.addons);
    const [addonItemsId, setAddonItemsId] = useState(product.addon_item_id_array);
    // const [addonItemIdsObject, setAddonItemIdsObject] = useState({});
    const [selectedAddonItemObjectArray, setSelectedAddonItemObjectArray] = useState([]);
    const [errors, setErrors] = useState({
        product_cost: '',
        discount: '',
        orderTax: ''
    });

    const saleUnitsOption = productUnits && productUnits.length && productUnits.map((productUnit) => {
        return {value: productUnit.id, label: productUnit.attributes.name}
    });
    if (!cartProduct) {
        return ''
    }

    useEffect(() => {
        console.log(product.addons, 'productaddons')
        setSaleUnitType(productUnits && productUnits.length && productUnits.filter((item) =>
            Number(item.id) === Number(product.sale_unit && product.sale_unit.value ? product.sale_unit.value : product.sale_unit)).map((item) => {
            return ({
                label: item.attributes.name,
                value: item.id
            })
        }))
    }, [productUnits]);

    useEffect(() => {
        productUnitDropdown(product.product_unit);
    }, []);

    useEffect(() => {
        setProduct(cartProduct);
        setUnitPrice(product.product_price && parseFloat(product.product_price).toFixed(2));
        setDiscount(product.discount_value ? (product.discount_value).toFixed(2) : discount);
        setOrderTax(parseFloat(product.tax_value).toFixed(2));
        setTaxType(product.tax_type === 1 || product.tax_type === '1' ? {
            value: 1, label: getFormattedMessage("tax-type.filter.exclusive.label")
        } : {
            value: 2, label: getFormattedMessage("tax-type.filter.inclusive.label")
        } || product.tax_type === 2 || product.tax_type === '2' ? {
            value: 2, label: getFormattedMessage("tax-type.filter.inclusive.label")
        } : {value: 1, label: getFormattedMessage("tax-type.filter.exclusive.label")});

        setDiscountType(product.discount_type === 1 ? {
            value: 1, label: getFormattedMessage("discount-type.filter.percentage.label")
        } : {
            value: 2,
            label: getFormattedMessage("discount-type.filter.fixed.label")
        } || product.discount_type === 2 ? {
            value: 2, label: getFormattedMessage("discount-type.filter.fixed.label")
        } : {value: 1, label: getFormattedMessage("discount-type.filter.percentage.label")});
    }, [cartProduct]);

    const handleValidation = () => {
        let errorss = {};
        let isValid = false;
        if (!unitPrice) {
            errorss['product_cost'] = 'Please enter price';
        } else if (discountType.value === 1 && discount > 100) {
            errorss['discount'] = 'The Discount must not be greater than 100';
        } else if (discountType.value === 2 && discount > Number(unitPrice)) {
            errorss['discount'] = 'The Discount must not be greater than product price';
        } else if (taxType.value === '1' && Number(orderTax) > 100) {
            errorss['orderTax'] = 'The Tax must not be greater than 100';
        } else {
            isValid = true;
        }
        setErrors(errorss);
        return isValid;
    };

    const onChangeUnitPrice = (e) => {
        const {value} = e.target;
        // check if value includes a decimal point
        if (value.match(/\./g)) {
            const [, decimal] = value.split('.');
            // restrict value to only 2 decimal places
            if (decimal?.length > 2) {
                // do nothing
                return;
            }
        }
        setUnitPrice(e.target.value);
    };

    //onChange tax field
    const onChangeTax = (e) => {
        const {value} = e.target;
        // check if value includes a decimal point
        if (value.match(/\./g)) {
            const [, decimal] = value.split('.');
            // restrict value to only 2 decimal places
            if (decimal?.length > 2) {
                // do nothing
                return;
            }
        }
        setOrderTax(value);
    };

    // tax type dropdown functionality
    const taxTypeFilterOptions = getFormattedOptions(taxMethodOptions)
    const [taxType, setTaxType] = useState(product.tax_type === 1 ? {
        value: 1, label: getFormattedMessage("tax-type.filter.exclusive.label")
    } : {
        value: 2, label: getFormattedMessage("tax-type.filter.inclusive.label")
    } || product.tax_type === 2 ? {
        value: 2, label: getFormattedMessage("tax-type.filter.inclusive.label")
    } : {
        value: 1, label: getFormattedMessage("tax-type.filter.exclusive.label")
    });
    const onTaxTypeChange = (obj) => {
        setTaxType(obj);
    };

    // discount type dropdown functionality
    const discountTypeFilterOptions = getFormattedOptions(discountMethodOptions)
    const [discountType, setDiscountType] = useState(product.discount_type === 1 ? {
        value: 1, label: getFormattedMessage("discount-type.filter.percentage.label")
    } : {
        value: 2,
        label: getFormattedMessage("discount-type.filter.fixed.label")
    } || product.discount_type === 2 ? {value: 2, label: getFormattedMessage("discount-type.filter.fixed.label")} : {
        value: 1, label: getFormattedMessage("discount-type.filter.percentage.label")
    });
    const onDiscountTypeChange = (obj) => {
        setDiscountType(obj);
    };

    const onChangeSaleUnitType = (obj) => {
        setSaleUnitType(obj);
    };

    //onChange discount field
    const onChangeDiscount = (e) => {
        const {value} = e.target;
        // check if value includes a decimal point
        if (value.match(/\./g)) {
            const [, decimal] = value.split('.');
            // restrict value to only 2 decimal places
            if (decimal?.length > 2) {
                return;
            }
        }
        setDiscount(value);
    };

    //discount amount function
    const discountAmount = (totalCost) => {
        let dis = 0;
        if (discount > 0 && discountType.value === '2' || discountType.value === 2) {
            dis = Number(discount)
        } else if (discount > 0 && discountType.value === '1' || discountType.value === 1) {
            const percentDiscount = discountType.value === '1' || discountType.value === 1 ? parseFloat(totalCost).toFixed(2) * Number(discount) / Number(100) : 0;
            dis = +percentDiscount;
        }
        return dis;
    };

    //tax amount function
    const taxAmount = (totalCost) => {
        const total = totalCost - discountAmount(product.product_price)
        let tax = 0;
        if (orderTax > 0 && taxType.value === '2' || taxType.value === 2) {
            tax = +totalCost
        } else if (orderTax > 0 && taxType.value === '1' || taxType.value === 1) {
            let exclusiveTax = taxType.value === '1' || taxType.value === 1 ? parseFloat(total).toFixed(2) * Number(orderTax) / Number(100) : 0;
            tax = +exclusiveTax;
        }
        return tax;
    };

    //product details save button function
    const onSaveDetailModal = () => {
        let addonItemId = selectedAddonItemObjectArray.map((object) => {
            return object.addonItemId;
        })
        let addonItemPrice = selectedAddonItemObjectArray.map((object) => {
            return object.addonItemPrice;
        })
        const totalAddonItemPrice = addonItemPrice.length > 0 ? addonItemPrice.reduce((accumulator, currentValue) => parseInt(accumulator) + parseInt(currentValue)) : 0;

        const newProduct = product;
        const Valid = handleValidation();
        if (Valid) {
            if (productModelId === product.id) {
                newProduct.net_unit_cost = calculateProductCost(product);
                newProduct.product_price = unitPrice;
                newProduct.discount_amount = discountAmount(product.product_price);
                newProduct.discount_value = Number(discount);
                newProduct.discount_type = (discountType.value);
                newProduct.tax_amount = taxAmount(product.product_price);
                // newProduct.tax_value = orderTax;
                newProduct.tax_value = isNaN(orderTax) ? 0 : orderTax;
                newProduct.tax_type = Number(taxType.value);
                newProduct.sale_unit = saleUnitType[0] ? saleUnitType[0].value : saleUnitType || saleUnitType ? saleUnitType.value : saleUnitType;
                newProduct.addons_value = addonItemId;
                newProduct.addons_price = totalAddonItemPrice;
                onProductUpdateInCart(newProduct);
            }
            updateCost(newProduct.net_unit_cost = calculateProductCost(unitPrice));
            openProductDetailModal(false);
        }
    };

    const toggleClass = (element) => {
        if (element.classList.contains('btn-secondary')) {
            element.classList.remove('btn-secondary');
        }else{
            element.classList.add('btn-secondary');
        }
    }

    const addonItemBtnHandler = (e) => {
        let addonItemId = e.target.dataset.addonItemId;
        let addonId = e.target.dataset.addonId;
        let addonItemPrice = e.target.dataset.addonItemPrice;
        //
        let objectFoundByAddonId = selectedAddonItemObjectArray.find(obj => obj.addonId === addonId);
        let objectFoundByAddonItemId = selectedAddonItemObjectArray.find(obj => obj.addonItemId === addonItemId);
        // let elementToRemove = object2;
        console.log(objectFoundByAddonItemId,'objectFoundByAddonItemId');
        let index = selectedAddonItemObjectArray.indexOf(objectFoundByAddonItemId);
        console.log(index,'index');

        toggleClass(e.target);


        if (index !== -1) {
            selectedAddonItemObjectArray.splice(index, 1);
            setSelectedAddonItemObjectArray(selectedAddonItemObjectArray);
            // e.target.classList.remove('btn-secondary');

            console.log(selectedAddonItemObjectArray,'selectedAddonItemObjectArray')
        }
        if (!objectFoundByAddonItemId) {

            if (objectFoundByAddonId) {
                const newArray = selectedAddonItemObjectArray.map(obj => {
                    if (obj.addonId === addonId) {
                        return {...obj, addonItemId: addonItemId, addonItemPrice: addonItemPrice};
                    }
                    return obj;
                });
                setSelectedAddonItemObjectArray(newArray);
            } else {
                setSelectedAddonItemObjectArray([...selectedAddonItemObjectArray, {
                    addonItemId: addonItemId,
                    addonId: addonId,
                    addonItemPrice: addonItemPrice
                }]);
            }

            // e.target.classList.add("btn-secondary");
            //
            // // Get all elements with the same class
            // var elements = document.querySelectorAll('.addon-item-btn');
            //
            // // Add click event listener to each element
            // elements.forEach(function(element) {
            //     element.addEventListener('click', function() {
            //         // Remove "active" class from all elements
            //         elements.forEach(function(element) {
            //             element.classList.remove('btn-secondary');
            //         });
            //         // Add "active" class to clicked element
            //         this.classList.add('btn-secondary');
            //     });
            // });

            console.log(selectedAddonItemObjectArray);

        }


    }
    return (
        <Modal show={isOpenCartItemUpdateModel} onHide={() => openProductDetailModal(false)} className="pos-modal">
            <Modal.Header closeButton>
                <Modal.Title className="text-capitalize">{product.name}</Modal.Title>
            </Modal.Header>
            <Modal.Body>
                <Form>
                    <div className='col-12'>
                        <Form.Group className='col-md-12' controlId='formAddon'>
                            <Form.Label>{getFormattedMessage('product.input.addon.label')}: </Form.Label>
                            {addons.length > 0 && addons.map((singleAddon) => {
                                return (
                                    <>
                                        <br/>
                                        <Form.Label className={'mt-2'}> {singleAddon.addon_name}</Form.Label>
                                        <br/>

                                        {singleAddon.addon_items && singleAddon.addon_items.map((singleAddonItem) => {
                                            return (<>
                                                    <Button
                                                        className={`mx-2 addon-item-btn ${selectedAddonItemObjectArray.map((single) => {
                                                            if (single.addonItemId == singleAddonItem.item_id) {
                                                                return ' btn-secondary '
                                                            }
                                                        })}`}
                                                        id="button-addon1"
                                                        data-addon-item-id={singleAddonItem.item_id}
                                                        data-addon-item-price={singleAddonItem.item_price}
                                                        data-addon-id={singleAddon.addon_id}
                                                        onClick={addonItemBtnHandler}>
                                                        {singleAddonItem.item_name} ${singleAddonItem.item_price}
                                                    </Button>

                                                </>

                                            )
                                        })}
                                    </>

                                )
                            })}

                        </Form.Group>
                    </div>
                </Form>
            </Modal.Body>
            <Modal.Footer className="pt-0">
                <Button variant='primary' onClick={() => onSaveDetailModal()}>
                    {getFormattedMessage("globally.save-btn")}
                </Button>
                <Button variant='secondary' className='me-0'
                        onClick={() => openProductDetailModal(false)}>
                    {getFormattedMessage('globally.cancel-btn')}
                </Button>
            </Modal.Footer>
        </Modal>
    )
};

const mapStateToProps = (state) => {
    const {productUnits} = state;
    return {productUnits}
};

export default connect(mapStateToProps, {productUnitDropdown})(ProductDetailsModel);


// old modal

// import React, {useEffect, useState} from 'react';
// import {Modal, Button} from 'react-bootstrap';
// import {Form, InputGroup} from 'react-bootstrap-v5';
// import Select from 'react-select';
// import {connect} from 'react-redux';
// import {decimalValidate, getFormattedMessage, placeholderText, getFormattedOptions} from '../../shared/sharedMethod';
// import {productUnitDropdown} from '../../store/action/productUnitAction';
// import ReactSelect from '../../shared/select/reactSelect';
// import {calculateProductCost} from './SharedMethod';
// import { taxMethodOptions, discountMethodOptions } from '../../constants';
//
// const ProductDetailsModel = (props) => {
//     const {
//         openProductDetailModal,
//         isOpenCartItemUpdateModel,
//         cartProduct,
//         onProductUpdateInCart,
//         productModelId,
//         updateCost,
//         productUnitDropdown,
//         productUnits,
//         frontSetting
//     } = props;
//
//     const [product, setProduct] = useState(cartProduct);
//     const [unitPrice, setUnitPrice] = useState(0);
//     const [saleUnitType, setSaleUnitType] = useState(null);
//     const [discount, setDiscount] = useState('0.00');
//     const [orderTax, setOrderTax] = useState(product.tax_value);
//     const [errors, setErrors] = useState({
//         product_cost: '',
//         discount: '',
//         orderTax: ''
//     });
//
//     const saleUnitsOption = productUnits && productUnits.length && productUnits.map((productUnit) => {
//         return {value: productUnit.id, label: productUnit.attributes.name}
//     });
//     if (!cartProduct) {
//         return ''
//     }
//
//     useEffect(() => {
//         setSaleUnitType(productUnits && productUnits.length && productUnits.filter((item) =>
//             Number(item.id) === Number(product.sale_unit && product.sale_unit.value ? product.sale_unit.value : product.sale_unit)).map((item) => {
//             return ({
//                 label: item.attributes.name,
//                 value: item.id
//             })
//         }))
//     }, [productUnits]);
//
//     useEffect(() => {
//         productUnitDropdown(product.product_unit);
//     }, []);
//
//     useEffect(() => {
//         setProduct(cartProduct);
//         setUnitPrice(product.product_price && parseFloat(product.product_price).toFixed(2));
//         setDiscount(product.discount_value ? (product.discount_value).toFixed(2) : discount);
//         setOrderTax(parseFloat(product.tax_value).toFixed(2));
//         setTaxType(product.tax_type === 1 || product.tax_type === '1' ? {
//             value: 1, label:  getFormattedMessage("tax-type.filter.exclusive.label")
//         } : {
//             value: 2, label: getFormattedMessage("tax-type.filter.inclusive.label")
//         } || product.tax_type === 2 || product.tax_type === '2' ? {
//             value: 2, label: getFormattedMessage("tax-type.filter.inclusive.label")
//         } : {value: 1,  label: getFormattedMessage("tax-type.filter.exclusive.label")});
//
//         setDiscountType(product.discount_type === 1 ? {
//             value: 1, label: getFormattedMessage("discount-type.filter.percentage.label")
//         } : {value: 2, label: getFormattedMessage("discount-type.filter.fixed.label")} || product.discount_type === 2 ? {
//             value: 2, label: getFormattedMessage("discount-type.filter.fixed.label")
//         } : {value: 1, label: getFormattedMessage("discount-type.filter.percentage.label")});
//     }, [cartProduct]);
//
//     const handleValidation = () => {
//         let errorss = {};
//         let isValid = false;
//         if (!unitPrice) {
//             errorss['product_cost'] = 'Please enter price';
//         } else if (discountType.value === 1 && discount > 100) {
//             errorss['discount'] = 'The Discount must not be greater than 100';
//         } else if (discountType.value === 2 && discount > Number(unitPrice)) {
//             errorss['discount'] = 'The Discount must not be greater than product price';
//         } else if (taxType.value === '1' && Number(orderTax) > 100) {
//             errorss['orderTax'] = 'The Tax must not be greater than 100';
//         } else {
//             isValid = true;
//         }
//         setErrors(errorss);
//         return isValid;
//     };
//
//     const onChangeUnitPrice = (e) => {
//         const {value} = e.target;
//         // check if value includes a decimal point
//         if (value.match(/\./g)) {
//             const [, decimal] = value.split('.');
//             // restrict value to only 2 decimal places
//             if (decimal?.length > 2) {
//                 // do nothing
//                 return;
//             }
//         }
//         setUnitPrice(e.target.value);
//     };
//
//     //onChange tax field
//     const onChangeTax = (e) => {
//         const {value} = e.target;
//         // check if value includes a decimal point
//         if (value.match(/\./g)) {
//             const [, decimal] = value.split('.');
//             // restrict value to only 2 decimal places
//             if (decimal?.length > 2) {
//                 // do nothing
//                 return;
//             }
//         }
//         setOrderTax(value);
//     };
//
//     // tax type dropdown functionality
//     const taxTypeFilterOptions = getFormattedOptions(taxMethodOptions)
//     const [taxType, setTaxType] = useState(product.tax_type === 1 ? {
//         value: 1, label: getFormattedMessage("tax-type.filter.exclusive.label")
//     } : {
//         value: 2, label: getFormattedMessage("tax-type.filter.inclusive.label")
//     } || product.tax_type === 2 ? {
//         value: 2, label: getFormattedMessage("tax-type.filter.inclusive.label")
//     } : {
//         value: 1, label: getFormattedMessage("tax-type.filter.exclusive.label")
//     });
//     const onTaxTypeChange = (obj) => {
//         setTaxType(obj);
//     };
//
//     // discount type dropdown functionality
//     const discountTypeFilterOptions = getFormattedOptions(discountMethodOptions)
//     const [discountType, setDiscountType] = useState(product.discount_type === 1 ? {
//         value: 1, label: getFormattedMessage("discount-type.filter.percentage.label")
//     } : {value: 2, label: getFormattedMessage("discount-type.filter.fixed.label")} || product.discount_type === 2 ? {value: 2, label: getFormattedMessage("discount-type.filter.fixed.label")} : {
//         value: 1, label: getFormattedMessage("discount-type.filter.percentage.label")
//     });
//     const onDiscountTypeChange = (obj) => {
//         setDiscountType(obj);
//     };
//
//     const onChangeSaleUnitType = (obj) => {
//         setSaleUnitType(obj);
//     };
//
//     //onChange discount field
//     const onChangeDiscount = (e) => {
//         const {value} = e.target;
//         // check if value includes a decimal point
//         if (value.match(/\./g)) {
//             const [, decimal] = value.split('.');
//             // restrict value to only 2 decimal places
//             if (decimal?.length > 2) {
//                 return;
//             }
//         }
//         setDiscount(value);
//     };
//
//     //discount amount function
//     const discountAmount = (totalCost) => {
//         let dis = 0;
//         if (discount > 0 && discountType.value === '2' || discountType.value === 2) {
//             dis = Number(discount)
//         } else if (discount > 0 && discountType.value === '1' || discountType.value === 1) {
//             const percentDiscount = discountType.value === '1' || discountType.value === 1 ? parseFloat(totalCost).toFixed(2) * Number(discount) / Number(100) : 0;
//             dis = +percentDiscount;
//         }
//         return dis;
//     };
//
//     //tax amount function
//     const taxAmount = (totalCost) => {
//         const total = totalCost - discountAmount(product.product_price)
//         let tax = 0;
//         if (orderTax > 0 && taxType.value === '2' || taxType.value === 2) {
//             tax = +totalCost
//         } else if (orderTax > 0 && taxType.value === '1' || taxType.value === 1) {
//             let exclusiveTax = taxType.value === '1' || taxType.value === 1 ? parseFloat(total).toFixed(2) * Number(orderTax) / Number(100) : 0;
//             tax = +exclusiveTax;
//         }
//         return tax;
//     };
//
//     //product details save button function
//     const onSaveDetailModal = () => {
//         const newProduct = product;
//         const Valid = handleValidation();
//         if (Valid) {
//             if (productModelId === product.id) {
//                 newProduct.net_unit_cost = calculateProductCost(product);
//                 newProduct.product_price = unitPrice;
//                 newProduct.discount_amount = discountAmount(product.product_price);
//                 newProduct.discount_value = Number(discount);
//                 newProduct.discount_type = (discountType.value);
//                 newProduct.tax_amount = taxAmount(product.product_price);
//                 newProduct.tax_value = orderTax;
//                 newProduct.tax_type = Number(taxType.value);
//                 newProduct.sale_unit = saleUnitType[0] ? saleUnitType[0].value : saleUnitType || saleUnitType ? saleUnitType.value : saleUnitType;
//                 onProductUpdateInCart(newProduct);
//             }
//             updateCost(newProduct.net_unit_cost = calculateProductCost(unitPrice));
//             openProductDetailModal(false);
//         }
//     };
//
//     return (
//         <Modal show={isOpenCartItemUpdateModel} onHide={() => openProductDetailModal(false)} className="pos-modal">
//             <Modal.Header closeButton>
//                 <Modal.Title className="text-capitalize">{product.name}</Modal.Title>
//             </Modal.Header>
//             <Modal.Body>
//                 <Form>
//                     <div className='col-12'>
//                         <Form.Group className='col-md-12 mb-3' controlId='formBasicProductCost'>
//                             <Form.Label>{getFormattedMessage('product.input.product-price.label')}: </Form.Label>
//                             <InputGroup>
//                                 <Form.Control type='text' name='product_cost' min='0' step='.01' placeholder='0.00'
//                                               onKeyPress={(event) => decimalValidate(event)}
//                                               className='form-control-solid' value={unitPrice}
//                                               onChange={(e) => onChangeUnitPrice(e)}
//                                 />
//                                 <InputGroup.Text>{frontSetting.value && frontSetting.value.currency_symbol}</InputGroup.Text>
//                             </InputGroup>
//                         </Form.Group>
//                         <div className='col-md-12 mb-3'>
//                             <ReactSelect  title={getFormattedMessage('product.input.tax-type.label')}
//                                     multiLanguageOption={taxTypeFilterOptions} onChange={onTaxTypeChange} errors={''}
//                                      defaultValue={taxType}
//                                      placeholder={placeholderText("product.input.tax-type.placeholder.label")}
//                         />
//                         </div>
//
//                         <Form.Group className='col-md-12 mb-3' controlId='formBasicOrderTax'>
//                             <Form.Label>{getFormattedMessage("product.product-details.tax.label")}: </Form.Label>
//                             <InputGroup>
//                                 <Form.Control type='text' name='orderTax' className='form-control-solid'
//                                               onKeyPress={(event) => decimalValidate(event)}
//                                               onChange={onChangeTax}  value={orderTax ? orderTax === 'NaN' ? '0.00' : orderTax : ''}/>
//                                 <InputGroup.Text>%</InputGroup.Text>
//                             </InputGroup>
//                             <span className='text-danger'>{errors['orderTax'] ? errors['orderTax'] : null}</span>
//                         </Form.Group>
//                         <div className='col-md-12 mb-3'>
//                             <ReactSelect  title={getFormattedMessage('purchase.product-modal.select.discount-type.label')}
//                                     multiLanguageOption={discountTypeFilterOptions} onChange={onDiscountTypeChange} errors={''}
//                                     defaultValue={discountType}
//                                     placeholder={placeholderText("pos-sale.select.discount-type.placeholder")}
//                             />
//                         </div>
//                         <Form.Group className='col-md-12 mb-3' controlId='formBasicDiscount'>
//                             <Form.Label>{getFormattedMessage('globally.detail.discount')}: </Form.Label>
//                             <Form.Control type='text' name='discount' min='0'
//                                           onKeyPress={(event) => decimalValidate(event)}
//                                           className='form-control-solid' max='100'
//                                           onChange={onChangeDiscount} value={discount ? discount : ''}/>
//                             <span
//                                 className='text-danger'>{errors['discount'] ? errors['discount'] : null}</span>
//                         </Form.Group>
//                         <Form.Group className='col-md-12' controlId='formBasicUnit'>
//                             <Form.Label>{getFormattedMessage('product.input.sale-unit.label')}: </Form.Label>
//                             <Select name='sale_unit' placeholder={placeholderText('pos-sale.select.sale-unit-type.placeholder')} value={saleUnitType}
//                                     onChange={onChangeSaleUnitType} options={saleUnitsOption} noOptionsMessage={() => getFormattedMessage('no-option.label')}
//                             />
//                         </Form.Group>
//                     </div>
//                 </Form>
//             </Modal.Body>
//             <Modal.Footer className="pt-0">
//                 <Button variant='primary' onClick={() => onSaveDetailModal()}>
//                     {getFormattedMessage("globally.save-btn")}
//                 </Button>
//                 <Button variant='secondary' className='me-0'
//                         onClick={() => openProductDetailModal(false)}>
//                     {getFormattedMessage('globally.cancel-btn')}
//                 </Button>
//             </Modal.Footer>
//         </Modal>
//     )
// };
//
// const mapStateToProps = (state) => {
//     const {productUnits} = state;
//     return {productUnits}
// };
//
// export default connect(mapStateToProps, {productUnitDropdown})(ProductDetailsModel);
