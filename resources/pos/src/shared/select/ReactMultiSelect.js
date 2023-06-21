import React, { CSSProperties } from "react";
import { Form } from "react-bootstrap-v5";

import makeAnimated from 'react-select/animated';

const animatedComponents = makeAnimated();

import Select from "react-select";

const groupStyles = {
  display: "flex",
  alignItems: "center",
  justifyContent: "space-between",
  textAlign:'center',
};


const formatGroupLabel = (data) => (
  <div style={groupStyles}>
    <h5>----{data.label}</h5>
  </div>
);

const  ReactMultiSelect = (props) => {
    const {title,isRequired,groupedOptions, handleSelectChange,defaultValue} = props;

    return  (
        <Form.Group className='form-group w-100' controlId='formBasic'>
        {title ? <Form.Label>{title} :</Form.Label> : ''}
        {isRequired ? '' : <span className='required'/>}
        <Select
          closeMenuOnSelect={false}
          components={animatedComponents}
          options={groupedOptions}
          isMulti
          formatGroupLabel={formatGroupLabel}
          onChange={handleSelectChange}
          defaultValue={defaultValue}
        />
        {/* { errors ? <span className='text-danger d-block fw-400 fs-small mt-2'>{errors ? errors : null}</span> : null} */}
      </Form.Group>
      );
 }

 export default ReactMultiSelect;
