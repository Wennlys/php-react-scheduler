import styled from 'styled-components'

export const Container = styled.div`
    background: #fff;
    padding: 0 30px;
`

export const Content = styled.div`
    height: 64px;
    max-width: 900px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    
    button {
      border: 0;
      background: none;
    }
    
    button:after {
      content: "";
      display: ${props => props.unread ? 'inline-block' : 'none'};
      position: absolute;
      margin-left: -5px;
      width: 10px;
      height: 10px;
      background: #ff892e;
      border-radius: 50%;
    }
    
      img {
        width: 40px;
        height: 40px;
      }
      
      img[alt="menu"] {
        cursor: pointer;
      }
      
      img[alt="logo"] {
        margin-left: -190px;
      }
`

export const Profile = styled.div`
      display: flex;
      align-items: center;

      hr {
        height: 50px;
        margin: 0 25px;
      }      
      
      img {
        border-radius: 50%;
      }
`
