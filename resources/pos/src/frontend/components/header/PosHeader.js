import React from 'react';
import CustomerDropDown from "../pos-dropdown/CustomerDropdown";
import WarehouseDropDown from "../pos-dropdown/WarehouseDropDown";
import {Button, Row} from "react-bootstrap-v5";


const PosHeader = (props) => {
    const {setSelectedCustomerOption,selectedCustomerOption, setSelectedOption, selectedOption, customerModel, updateCustomer} = props;
    const customStyles = {
        control: (provided, state) => ({
          ...provided,
          borderRadius: '20px',
          padding: '11px 35px'
        })
      };
    return (
        <div className='top-nav h-auto py-3 bg-custom-grey'>
            <Row className="align-items-center justify-content-between grp-select h-100">
                <CustomerDropDown setSelectedCustomerOption={setSelectedCustomerOption}
                                  selectedCustomerOption={selectedCustomerOption} customerModel={customerModel}
                                  updateCustomer={updateCustomer} customStyles={customStyles}/>

                <WarehouseDropDown setSelectedOption={setSelectedOption}
                                   selectedOption={selectedOption} customStyles={customStyles}/>
            </Row>
        </div>
    )
};
export default PosHeader
