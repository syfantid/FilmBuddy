package movies_component;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import models.*;

/**
 * Class to handle the MySQL Database functionality
 * Created by Sofia on 4/3/2016.
 */
public class MovieStorager {
    private static Connection conn;

    /**
     * Constructor of the class; initializes the connection to the Database
     */
    public MovieStorager() {
        MySQLDatabase db = new MySQLDatabase();
        if (db.connect()) {
            conn = db.getConnection();
        }
    }

    /**
     * Closes the connection to the Database
     * @return True if the close was successful, false otherwise
     */
    public boolean closeConnection() {
        try {
            conn.close();
            return true;
        } catch (SQLException e) {
            return false;
        }
    }

    /**
     * Inserts a movie into the Database
     * @param m The movie to be inserted
     * @throws SQLException In case a connection is not open or there is a problem with the inserted values
     */
    public void InsertMovietoDB(Movie m) throws SQLException {
        // The mysql insert statement
        String query = " insert into all_movies (title, year, categories, synopsis, icon_url, cast, " +
                "director, imdb_url, extended_plot)" + " values (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        // Create the mysql insert PreparedStatement
        PreparedStatement preparedStmt = conn.prepareStatement(query);
        preparedStmt.setString(1, m.getTitle());
        preparedStmt.setInt(2, m.getYear());
        preparedStmt.setString(3, m.getCategories());
        preparedStmt.setString(4, m.getSynopsis());
        preparedStmt.setString(5, m.getIconURL());
        preparedStmt.setString(6, m.getCast());
        preparedStmt.setString(7, m.getDirector());
        preparedStmt.setString(8, m.getImdbURL());
        preparedStmt.setString(9, m.getExtendedPlot());

            // Execute the PreparedStatement
        try {
            preparedStmt.execute();
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    /**
     * Deletes a row from the Database
     * @param id The row's ID
     */
    public void deleteMovie(String id) {
        String query = " DELETE FROM `all_movies` WHERE id= ? ";
        PreparedStatement preparedStmtAux;
        try {
            preparedStmtAux = conn.prepareStatement(query);
            preparedStmtAux.setString(1, id);
            preparedStmtAux.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    /**
     * Inserts the film's semantics_plot column value
     * @param id The ID of the film
     * @param semantics The semantics plot
     */
    public void insertSemanticPlot(String id, String semantics) {
        String query = " UPDATE `all_movies` SET `semantics_plot`= ? WHERE `id`= ? ";
        PreparedStatement preparedStmtAux;
        try {
            preparedStmtAux = conn.prepareStatement(query);
            preparedStmtAux.setString(1, semantics);
            preparedStmtAux.setString(2, id);
            preparedStmtAux.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }


    }

    /**
     * Executes a result query and returns the results
     * @param query The query to be executed in a String format
     * @return The results in a ResultSet format
     */
    public ResultSet selectQuery(String query){
        ResultSet set = null;
        try {
            // Create the mysql select PreparedStatement
            PreparedStatement statement = conn.prepareStatement(query);
            // Execute the PreparedStatement
            set = statement.executeQuery(query);
        } catch (Exception e) {
            System.out.println("Exception in query method:\n" + e.getMessage());
        }
        return set;
    }
}
