import React, { useEffect, useState } from 'react';
import { connect } from 'react-redux';
import { useParams } from 'react-router-dom'
import { fetchProduct } from '../../store/action/productAction';
import ProductForm from './ProductForm';
import HeaderTitle from '../header/HeaderTitle';
import MasterLayout from '../MasterLayout';
import { productUnitDropdown } from '../../store/action/productUnitAction';
import { fetchAllunits } from '../../store/action/unitsAction';
import { getFormattedMessage } from '../../shared/sharedMethod';
import TopProgressBar from "../../shared/components/loaders/TopProgressBar";
import { Filters } from '../../constants';
import { fetchAllBaseUnits } from "../../store/action/baseUnitsAction";

const EditProduct = (props) => {
    const { fetchProduct, products, fetchAllBaseUnits, base } = props;
    const { id } = useParams();
    useEffect(() => {
        fetchAllBaseUnits();
        fetchProduct(id);
    }, []);
    const getSaleUnit = products.length >= 1 && products[0]?.attributes.sale_unit_name ? { label: products[0]?.attributes.sale_unit_name.name, value: products[0]?.attributes.sale_unit_name.id } : ''
    const getPurchaseUnit = products.length >= 1 && products[0]?.attributes.sale_unit_name ? { label: products[0]?.attributes.purchase_unit_name.name, value: products[0]?.attributes.purchase_unit_name.id } : ''

    const itemsValue = products.length === 1 && products.map(product => ({
        name: product?.attributes.name,
        code: product?.attributes.code,
        product_category_id: {
            value: product?.attributes.product_category_id,
            label: product?.attributes.product_category_name
        },
        product_subcategory_id: {
            value: product?.attributes.product_subcategory_id,
            label: product?.attributes.product_subcategory_name
        },
        brand_id: {
            value: product?.attributes.brand_id,
            label: product?.attributes.brand_name
        },
        barcode_symbol: product?.attributes.barcode_symbol,
        product_cost: product?.attributes.product_cost,
        product_price: product?.attributes.product_price,
        product_unit: Number(product?.attributes.product_unit),
        sale_unit: getSaleUnit,
        purchase_unit: getPurchaseUnit,
        stock_alert: product?.attributes.stock_alert,
        order_tax: product?.attributes.order_tax,
        tax_type: product?.attributes.tax_type,
        notes: product?.attributes.notes,
        images: product?.attributes.images,
        quantity_limit: product?.attributes.quantity_limit,
        // making_time: '1212',  todo getting error while pass a value here .
        is_Edit: true,
        id: product.id,
        product_addons:product?.attributes.product_addons
        ,


    }));
    const getProductUnit = itemsValue && base.filter((fill) => Number(fill?.id) === Number(itemsValue[0]?.product_unit))
    
    return (
        <MasterLayout>
            <TopProgressBar />
            <HeaderTitle title={getFormattedMessage('product.edit.title')} to='/app/products' />
            {itemsValue.length >= 1 && <ProductForm singleProduct={itemsValue} productUnit={getProductUnit} baseUnits={base} id={id} />}
        </MasterLayout>
    )
};

const mapStateToProps = (state) => {
    const { products, base } = state;
    return { products, base }
};

export default connect(mapStateToProps, { fetchProduct, fetchAllBaseUnits, productUnitDropdown, fetchAllunits })(EditProduct);

